<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\GovernmentOffice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeProfileController extends Controller
{
    private const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function index(): View
    {
        $offices = auth()->user()->governmentOffices()->orderBy('government_offices.name')->get();

        if ($offices->isNotEmpty()) {
            $ids = $offices->pluck('id')->all();
            $sid = session('office_context_id');
            if (! $sid || ! in_array((int) $sid, $ids, true)) {
                session(['office_context_id' => $offices->first()->id]);
            }
        }

        return view('office.profile.index', compact('offices'));
    }

    public function edit(GovernmentOffice $office): View
    {
        $office->load('municipality:id,name');

        $hoursByDay = [];
        foreach (self::WEEKDAYS as $day) {
            $hoursByDay[$day] = null;
        }
        foreach ($office->working_hours ?? [] as $row) {
            if (! empty($row['day'])) {
                $hoursByDay[$row['day']] = [
                    'open' => $row['open'] ?? '',
                    'close' => $row['close'] ?? '',
                ];
            }
        }

        $contact = $office->contact_info ?? [];

        return view('office.profile.edit', [
            'office' => $office,
            'weekdays' => self::WEEKDAYS,
            'hoursByDay' => $hoursByDay,
            'contact' => $contact,
        ]);
    }

    public function update(Request $request, GovernmentOffice $office): RedirectResponse
    {
        foreach (['email', 'phone', 'website', 'google_maps_url', 'latitude', 'longitude'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_fax' => ['nullable', 'string', 'max:50'],
            'contact_hotline' => ['nullable', 'string', 'max:50'],
            'contact_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $workingHours = [];
        foreach (self::WEEKDAYS as $day) {
            $closedKey = 'wh_'.$day.'_closed';
            if ($request->boolean($closedKey)) {
                continue;
            }
            $open = $request->input('wh_'.$day.'_open');
            $close = $request->input('wh_'.$day.'_close');
            if (is_string($open) && is_string($close) && $open !== '' && $close !== '') {
                $workingHours[] = [
                    'day' => $day,
                    'open' => $open,
                    'close' => $close,
                ];
            }
        }

        $contactInfo = array_filter([
            'fax' => $validated['contact_fax'] ?? null,
            'hotline' => $validated['contact_hotline'] ?? null,
            'notes' => $validated['contact_notes'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $office->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'website' => $validated['website'] ?? null,
            'google_maps_url' => $validated['google_maps_url'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'working_hours' => $workingHours ?: null,
            'contact_info' => $contactInfo ?: null,
        ]);

        return redirect()
            ->route('office.profile.edit', $office)
            ->with('success', 'Office profile updated successfully.');
    }
}
