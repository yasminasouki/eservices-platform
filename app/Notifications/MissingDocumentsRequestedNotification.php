<?php

namespace App\Notifications;

use App\Models\GovernmentOffice;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/** Sent to the citizen when staff marks a request as missing required documents. */
class MissingDocumentsRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly GovernmentOffice $office,
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
            'message' => "{$this->office->name} marked your request #{$this->serviceRequest->id} as missing documents. Please upload what was requested.",
            'request_id' => $this->serviceRequest->id,
            'office_id' => $this->office->id,
            'url' => route('citizen.requests.show', $this->serviceRequest),
        ];
    }
}
