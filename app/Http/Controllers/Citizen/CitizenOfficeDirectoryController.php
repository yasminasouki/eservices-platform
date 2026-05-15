<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\GovernmentOffice;
use App\Models\Municipality;
use App\Models\OfficerTimeSlot;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CitizenOfficeDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $municipalityId = $request->input('municipality');

        $offices = GovernmentOffice::query()
            ->where('is_active', true)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($municipalityId, fn ($q) => $q->where('municipality_id', $municipalityId))
            ->withCount(['services' => fn ($q) => $q->where('is_active', true)])
            ->with('municipality:id,name')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $municipalities = Municipality::orderBy('name')->get(['id', 'name']);

        $mapMarkers = GovernmentOffice::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount(['services' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'latitude', 'longitude', 'address'])
            ->map(fn (GovernmentOffice $o) => [
                'id' => $o->id,
                'name' => $o->name,
                'lat' => (float) $o->latitude,
                'lng' => (float) $o->longitude,
                'address' => $o->address,
                'url' => route('citizen.offices.show', $o),
                'services' => (int) $o->services_count,
            ])
            ->values();

        $mapConfig = [
            'defaultLat' => config('maps.default_center.lat'),
            'defaultLng' => config('maps.default_center.lng'),
            'tileUrl' => config('maps.tile_url'),
            'attribution' => config('maps.tile_attribution'),
            'maxZoom' => config('maps.max_zoom'),
        ];

        return view('citizen.offices.index', compact('offices', 'mapMarkers', 'mapConfig', 'municipalities', 'search', 'municipalityId'));
    }

    public function show(GovernmentOffice $office): View
    {
        abort_unless($office->is_active, 404);

        $categories = $office->serviceCategories()
            ->whereHas('services', fn ($q) => $q->where('is_active', true))
            ->with([
                'services' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        $avgRating = Feedback::query()
            ->where('government_office_id', $office->id)
            ->whereNotNull('rating')
            ->avg('rating');

        $publicReviews = Feedback::query()
            ->where('government_office_id', $office->id)
            ->whereNotNull('rating')
            ->latest()
            ->limit(12)
            ->get(['id', 'user_id', 'rating', 'comment', 'office_reply', 'reply_is_public', 'replied_at', 'created_at']);

        $availableSlots = OfficerTimeSlot::query()
            ->where('government_office_id', $office->id)
            ->where('is_available', true)
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $myAppointments = Appointment::query()
            ->where('appointments.user_id', auth()->id())
            ->where('appointments.government_office_id', $office->id)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereHas('timeSlot', fn ($q) => $q->whereDate('date', '>=', today()))
            ->with('timeSlot')
            ->join('officer_time_slots', 'appointments.officer_time_slot_id', '=', 'officer_time_slots.id')
            ->orderBy('officer_time_slots.date')
            ->orderBy('officer_time_slots.start_time')
            ->select('appointments.*')
            ->get();

        $mapConfig = [
            'defaultLat' => config('maps.default_center.lat'),
            'defaultLng' => config('maps.default_center.lng'),
            'tileUrl' => config('maps.tile_url'),
            'attribution' => config('maps.tile_attribution'),
            'maxZoom' => config('maps.max_zoom'),
        ];

        $officeMap = null;
        if ($office->latitude !== null && $office->longitude !== null) {
            $officeMap = [
                'lat' => (float) $office->latitude,
                'lng' => (float) $office->longitude,
                'name' => $office->name,
                'address' => $office->address,
            ];
        }

        return view('citizen.offices.show', [
            'office' => $office,
            'categories' => $categories,
            'avgRating' => $avgRating !== null ? round((float) $avgRating, 1) : null,
            'publicReviews' => $publicReviews,
            'availableSlots' => $availableSlots,
            'myAppointments' => $myAppointments,
            'mapConfig' => $mapConfig,
            'officeMap' => $officeMap,
        ]);
    }
}
