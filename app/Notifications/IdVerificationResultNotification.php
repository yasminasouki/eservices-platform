<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class IdVerificationResultNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string  $status,
        private readonly ?string $reason = null,
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
        $message = $this->status === 'verified'
            ? 'Your ID has been verified successfully!'
            : "Your ID verification was rejected. Reason: {$this->reason}";

        return [
            'message' => $message,
            'status'  => $this->status,
            'url'     => route('citizen.id.verify'),
        ];
    }
}
