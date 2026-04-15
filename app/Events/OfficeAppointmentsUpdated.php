<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfficeAppointmentsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $officeId,
        public string $action,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('appointments.office.'.$this->officeId);
    }

    public function broadcastAs(): string
    {
        return 'appointments.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'office_id' => $this->officeId,
            'action' => $this->action,
        ];
    }
}
