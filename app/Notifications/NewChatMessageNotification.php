<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewChatMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $senderName,
        private readonly string $bodyPreview,
        private readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $preview = Str::limit(trim($this->bodyPreview), 120);

        return [
            'message' => "{$this->senderName}: {$preview}",
            'url' => $this->url,
        ];
    }
}
