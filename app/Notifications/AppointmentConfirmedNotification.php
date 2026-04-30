<?php

namespace App\Notifications;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/** Sent to the citizen when office staff confirms their appointment. */
class AppointmentConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Appointment $appointment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    private function payload(): array
    {
        $this->appointment->loadMissing('governmentOffice', 'timeSlot');

        $office = $this->appointment->governmentOffice;
        $slot = $this->appointment->timeSlot;

        $officeName = $office?->name ?? 'the office';
        $dateStr = $slot ? Carbon::parse($slot->date)->format('D, j M Y') : '';
        $timeStr = $slot
            ? Carbon::parse($slot->start_time)->format('H:i').' – '.Carbon::parse($slot->end_time)->format('H:i')
            : '';
        $when = $dateStr && $timeStr ? " ({$dateStr}, {$timeStr})" : '';

        return [
            'message' => "{$officeName} has confirmed your appointment{$when}.",
            'appointment_id' => $this->appointment->id,
            'office_id' => $office?->id,
            'url' => $office
                ? route('citizen.offices.show', $office).'#office-appointments'
                : route('citizen.dashboard'),
        ];
    }
}
