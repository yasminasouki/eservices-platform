<?php

namespace App\Http\Controllers\Office;

use App\Events\OfficeAppointmentsUpdated;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\GovernmentOffice;
use App\Models\OfficerTimeSlot;
use App\Notifications\AppointmentConfirmedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $hasOverlap = OfficerTimeSlot::query()
            ->where('government_office_id', $office->id)
            ->whereDate('date', $request->date)
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time)
            ->exists();

        if ($hasOverlap) {
            return back()
                ->withInput()
                ->with('error', 'This time slot overlaps an existing slot. Please choose a different time.');
        }

        OfficerTimeSlot::create([
            'government_office_id' => $office->id,
            'user_id' => auth()->id(),
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_available' => true,
        ]);

        $this->dispatchAppointmentsUpdate($office->id, 'slot_added');

        return back()->with('success', 'Time slot added successfully.');
    }

    public function destroySlot(GovernmentOffice $office, OfficerTimeSlot $slot)
    {
        if (! $slot->is_available) {
            return back()->with('error', 'Cannot delete a booked slot.');
        }

        $slot->delete();

        $this->dispatchAppointmentsUpdate($office->id, 'slot_deleted');

        return back()->with('success', 'Time slot deleted successfully.');
    }

    public function appointments(GovernmentOffice $office)
    {
        $appointments = Appointment::whereHas('timeSlot', fn ($q) => $q->where('government_office_id', $office->id))
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
        abort_unless($appointment->government_office_id === $office->id, 404);

        if ($appointment->status !== 'scheduled') {
            return back()->with('error', 'Only scheduled appointments can be confirmed.');
        }

        $appointment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $appointment->citizen?->notify(new AppointmentConfirmedNotification($appointment));

        $this->dispatchAppointmentsUpdate($office->id, 'confirmed');

        return back()->with('success', 'Appointment confirmed.');
    }

    public function cancelAppointment(Request $request, GovernmentOffice $office, Appointment $appointment): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_at' => now(),
        ]);

        $appointment->timeSlot->update(['is_available' => true]);

        $this->dispatchAppointmentsUpdate($office->id, 'cancelled_by_office');

        return back()->with('success', 'Appointment cancelled.');
    }

    private function dispatchAppointmentsUpdate(int $officeId, string $action): void
    {
        try {
            OfficeAppointmentsUpdated::dispatch($officeId, $action);
        } catch (\Throwable $e) {
            // Do not fail CRUD actions when websocket server is unavailable.
            Log::warning('Appointments live update broadcast failed.', [
                'office_id' => $officeId,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
