<?php

namespace App\Http\Controllers\Citizen;

use App\Events\OfficeAppointmentsUpdated;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\GovernmentOffice;
use App\Models\OfficerTimeSlot;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        if ($slot->government_office_id !== $office->id) {
            abort(404);
        }

        $appointment = null;

        try {
            DB::transaction(function () use ($slot, $office, &$appointment): void {
                $lockedSlot = OfficerTimeSlot::query()
                    ->whereKey($slot->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedSlot->is_available) {
                    throw new \RuntimeException('This slot is no longer available.');
                }

                $overlapsExisting = Appointment::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->whereHas('timeSlot', function ($query) use ($lockedSlot): void {
                        $query->whereDate('date', $lockedSlot->date)
                            ->where('start_time', '<', $lockedSlot->end_time)
                            ->where('end_time', '>', $lockedSlot->start_time);
                    })
                    ->exists();

                if ($overlapsExisting) {
                    throw new \RuntimeException('You already have an overlapping appointment at this time.');
                }

                $appointment = Appointment::create([
                    'user_id' => auth()->id(),
                    'government_office_id' => $office->id,
                    'officer_time_slot_id' => $lockedSlot->id,
                    'status' => 'scheduled',
                ]);

                $lockedSlot->update(['is_available' => false]);
            });
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sorry, this slot was just booked by someone else.'], 409);
            }

            return back()->with('error', 'Sorry, this slot was just booked by someone else.');
        }

        if ($request->expectsJson()) {
            $this->dispatchAppointmentsUpdate($office->id, 'booked');

            return response()->json([
                'message' => 'Appointment booked successfully!',
                'slot_id' => $slot->id,
                'appointment_id' => $appointment?->id,
            ]);
        }

        $this->dispatchAppointmentsUpdate($office->id, 'booked');

        return redirect()->route('citizen.requests.index')
            ->with('success', 'Appointment booked successfully!');
    }

    public function cancel(Request $request, GovernmentOffice $office, Appointment $appointment)
    {
        abort_unless((int) $appointment->user_id === (int) auth()->id(), 404);
        abort_unless((int) $appointment->government_office_id === (int) $office->id, 404);

        if (! in_array($appointment->status, ['scheduled', 'confirmed'], true)) {
            return back()->with('error', 'Only scheduled or confirmed appointments can be cancelled.');
        }

        $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($appointment, $request): void {
            $appointment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->input('cancellation_reason') ?: 'Cancelled by citizen.',
                'cancelled_at' => now(),
            ]);

            $appointment->timeSlot()->update(['is_available' => true]);
        });

        $this->dispatchAppointmentsUpdate($office->id, 'cancelled_by_citizen');

        return back()->with('success', 'Appointment cancelled.');
    }

    private function dispatchAppointmentsUpdate(int $officeId, string $action): void
    {
        try {
            OfficeAppointmentsUpdated::dispatch($officeId, $action);
        } catch (\Throwable $e) {
            // Keep booking/cancel flows successful even when Reverb is down.
            Log::warning('Appointments live update broadcast failed.', [
                'office_id' => $officeId,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
