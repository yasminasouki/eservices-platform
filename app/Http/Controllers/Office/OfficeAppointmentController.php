<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\GovernmentOffice;
use App\Models\OfficerTimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfficeAppointmentController extends Controller
{
    public function index(GovernmentOffice $office)
    {
        $slots = OfficerTimeSlot::where('government_office_id', $office->id)
            ->with('appointment')
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(15);

        return view('office.appointments.index', compact('office', 'slots'));
    }

    public function storeSlot(Request $request, GovernmentOffice $office)
    {
        $request->validate([
            'date'       => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        OfficerTimeSlot::create([
            'government_office_id' => $office->id,
            'user_id'              => auth()->id(),
            'date'                 => $request->date,
            'start_time'           => $request->start_time,
            'end_time'             => $request->end_time,
            'is_available'         => true,
        ]);

        return back()->with('success', 'Time slot added successfully.');
    }

    public function destroySlot(GovernmentOffice $office, OfficerTimeSlot $slot)
    {
        if (!$slot->is_available) {
            return back()->with('error', 'Cannot delete a booked slot.');
        }

        $slot->delete();

        return back()->with('success', 'Time slot deleted successfully.');
    }

    public function appointments(GovernmentOffice $office)
    {
        $appointments = Appointment::whereHas('timeSlot', fn($q) => $q->where('government_office_id', $office->id))
            ->with(['citizen', 'timeSlot', 'serviceRequest'])
            ->join('officer_time_slots', 'appointments.officer_time_slot_id', '=', 'officer_time_slots.id')
            ->orderBy('officer_time_slots.date')
            ->orderBy('officer_time_slots.start_time')
            ->select('appointments.*')
            ->paginate(15);

        return view('office.appointments.list', compact('office', 'appointments'));
    }

    public function confirmAppointment(GovernmentOffice $office, Appointment $appointment): RedirectResponse
    {
        $appointment->update([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Appointment confirmed.');
    }

    public function cancelAppointment(Request $request, GovernmentOffice $office, Appointment $appointment): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $appointment->update([
            'status'               => 'cancelled',
            'cancellation_reason'  => $request->cancellation_reason,
            'cancelled_at'         => now(),
        ]);

        $appointment->timeSlot->update(['is_available' => true]);

        return back()->with('success', 'Appointment cancelled.');
    }
}
