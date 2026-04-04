<?php

namespace App\Broadcasting;

use App\Events\OfficeChatMessageSent;
use Illuminate\Broadcasting\BroadcastException;

final class OfficeChatBroadcaster
{
    /**
     * Broadcasts the event to Reverb. If the WebSocket server is not running,
     * the message is still persisted; only real-time delivery fails.
     */
    public static function broadcast(OfficeChatMessageSent $event): void
    {
        try {
            broadcast($event);
        } catch (BroadcastException $e) {
            logger()->warning('Office chat broadcast failed. For real-time updates, run `php artisan reverb:start` (or use `composer run dev`).', [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
