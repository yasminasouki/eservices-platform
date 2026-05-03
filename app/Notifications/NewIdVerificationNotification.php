<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewIdVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $citizenName,
        private readonly int    $citizenId,
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
            'message'      => "{$this->citizenName} uploaded their ID and is awaiting verification.",
            'citizen_name' => $this->citizenName,
            'citizen_id'   => $this->citizenId,
            'url'          => route('admin.citizens.show', $this->citizenId),
        ];
    }
}
