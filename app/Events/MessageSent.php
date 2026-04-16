<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private Message $message,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('room.' . $this->message->room_id);
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->message->type,
            'nick' => $this->message->nick,
            'content' => $this->message->content,
            'file_url' => $this->message->file_path
                ? asset('storage/' . $this->message->file_path)
                : null,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
