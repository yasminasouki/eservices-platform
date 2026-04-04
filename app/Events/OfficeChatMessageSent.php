<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfficeChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $this->message->loadMissing(['sender:id,name,role', 'governmentOffice:id']);

        $officeId = $this->message->government_office_id;
        $citizenId = $this->resolveCitizenUserId();

        return [
            new PrivateChannel('office-chat.'.$officeId.'.'.$citizenId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->message->loadMissing('sender:id,name');

        return [
            'id' => $this->message->id,
            'government_office_id' => $this->message->government_office_id,
            'body' => $this->message->body,
            'sender_id' => $this->message->sender_id,
            'sender' => [
                'id' => $this->message->sender?->id,
                'name' => $this->message->sender?->name ?? 'User',
            ],
            'created_at' => $this->message->created_at?->toIso8601String(),
        ];
    }

    private function resolveCitizenUserId(): int
    {
        $this->message->loadMissing('sender:id,role', 'receiver:id,role');

        if ($this->message->sender && $this->message->sender->isCitizen()) {
            return (int) $this->message->sender_id;
        }

        if ($this->message->receiver && $this->message->receiver->isCitizen()) {
            return (int) $this->message->receiver_id;
        }

        return (int) $this->message->sender_id;
    }
}
