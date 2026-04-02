<?php

namespace App\Notifications;

use App\Models\GovernmentOffice;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfficeAddedDocumentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly GovernmentOffice $office,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $officeName = $this->office->name;

        return [
            'message' => "{$officeName} uploaded a document for your service request.",
            'request_id' => $this->serviceRequest->id,
            'office_id' => $this->office->id,
            'url' => route('citizen.requests.show', $this->serviceRequest),
        ];
    }
}
