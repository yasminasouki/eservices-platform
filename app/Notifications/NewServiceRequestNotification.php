<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewServiceRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $citizenName,
        private readonly string $serviceName,
        private readonly int    $requestId,
        private readonly int    $officeId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message'      => "{$this->citizenName} submitted a new request for \"{$this->serviceName}\".",
            'citizen_name' => $this->citizenName,
            'service_name' => $this->serviceName,
            'request_id'   => $this->requestId,
            'office_id'    => $this->officeId,
            'url'          => route('office.requests.show', [$this->officeId, $this->requestId]),
        ];
    }
}
