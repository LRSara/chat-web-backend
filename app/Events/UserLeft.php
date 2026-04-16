<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLeft implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private int $roomId,
        private string $nick,
        private array $usersOnline,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastWith(): array
    {
        return [
            'nick' => $this->nick,
            'users_online' => $this->usersOnline,
        ];
    }
}
