<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\GovernmentOffice;
use App\Models\OfficerTimeSlot;
use Illuminate\Http\Request;

class CitizenAppointmentController extends Controller
{
    public function book(GovernmentOffice $office)
    {
        // Available slots are passed via CitizenOfficeDirectoryController::show().
        // This route simply redirects to the office page where the slots are displayed.
        return redirect()->route('citizen.offices.show', $office)->withFragment('office-appointments');
    }

    public function store(Request $request, GovernmentOffice $office, OfficerTimeSlot $slot)
    {
        if (! $slot->is_available) {
            return back()->with('error', 'Sorry, this slot is no longer available.');
        }

        Appointment::create([
            'user_id' => auth()->id(),
            'government_office_id' => $office->id,
            'officer_time_slot_id' => $slot->id,
            'status' => 'scheduled',
        ]);

        $slot->update(['is_available' => false]);

        return redirect()->route('citizen.requests.index')
            ->with('success', 'Appointment booked successfully!');
    }
}
