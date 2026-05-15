<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledByCitizenNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $citizenName,
        private readonly string $date,
        private readonly string $time,
        private readonly int    $officeId,
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
        return [
            'message'      => "{$this->citizenName} cancelled their appointment on {$this->date} at {$this->time}.",
            'citizen_name' => $this->citizenName,
            'date'         => $this->date,
            'time'         => $this->time,
            'office_id'    => $this->officeId,
            'url'          => route('office.appointments.index', $this->officeId),
        ];
    }
}
