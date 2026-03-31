<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send email reminders for appointments scheduled tomorrow';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $appointments = Appointment::whereIn('status', ['scheduled', 'confirmed'])
            ->whereNull('reminder_sent_at')
            ->whereHas('timeSlot', fn ($q) => $q->whereDate('date', $tomorrow))
            ->with(['citizen', 'governmentOffice', 'timeSlot'])
            ->get();

        foreach ($appointments as $appointment) {
            Mail::to($appointment->citizen->email)
                ->send(new AppointmentReminderMail($appointment));

            $appointment->update(['reminder_sent_at' => now()]);

            Log::info("Reminder sent to {$appointment->citizen->email}");
        }

        $this->info("Sent {$appointments->count()} appointment reminder(s).");
    }
}
