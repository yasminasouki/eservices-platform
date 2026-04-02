<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** Notify office staff when a citizen adds documents to an existing request (not used on initial submit). */
class NewDocumentUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $citizenName,
        private readonly int $requestId,
        private readonly int $officeId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "{$this->citizenName} uploaded a new document on request #{$this->requestId}.",
            'citizen_name' => $this->citizenName,
            'request_id' => $this->requestId,
            'office_id' => $this->officeId,
            'url' => route('office.requests.show', [$this->officeId, $this->requestId]),
        ];
    }
}
