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
        private readonly int $documentCount = 1,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $n = max(1, $this->documentCount);
        $docPhrase = $n === 1
            ? 'a new document'
            : $n.' new documents';

        return [
            'message' => "{$this->citizenName} uploaded {$docPhrase} on request #{$this->requestId}.",
            'citizen_name' => $this->citizenName,
            'request_id' => $this->requestId,
            'office_id' => $this->officeId,
            'document_count' => $n,
            'url' => route('office.requests.show', [$this->officeId, $this->requestId]),
        ];
    }
}
