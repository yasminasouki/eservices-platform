<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\GovernmentOffice;
use App\Models\OfficerTimeSlot;
use Illuminate\View\View;

class CitizenOfficeDirectoryController extends Controller
{
    public function index(): View
    {
        $offices = GovernmentOffice::query()
            ->where('is_active', true)
            ->withCount(['services' => fn ($q) => $q->where('is_active', true)])
            ->with('municipality:id,name')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

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

        return view('citizen.offices.index', compact('offices', 'mapMarkers', 'mapConfig'));
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
            'mapConfig' => $mapConfig,
            'officeMap' => $officeMap,
        ]);
    }
}
