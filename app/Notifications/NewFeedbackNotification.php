<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewFeedbackNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $citizenName,
        private readonly int    $rating,
        private readonly int    $officeId,
        private readonly ?int   $requestId = null,
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
        $context = $this->requestId
            ? " on request #{$this->requestId}"
            : ' for your office';

        return [
            'message'    => "{$this->citizenName} left {$this->rating}/5 star feedback{$context}.",
            'citizen_name' => $this->citizenName,
            'rating'     => $this->rating,
            'office_id'  => $this->officeId,
            'request_id' => $this->requestId,
            'url'        => route('office.feedback.index', $this->officeId),
        ];
    }
}
