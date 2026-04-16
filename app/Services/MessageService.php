<?php

namespace App\Services;

use App\Models\Message;
use App\Events\MessageSent;

class MessageService
{
    public function store(int $roomId, string $nick, string $type, ?string $content = null, ?string $filePath = null): Message
    {
        $message = Message::create([
            'room_id' => $roomId,
            'nick' => $nick,
            'type' => $type,
            'content' => $content,
            'file_path' => $filePath,
            'created_at' => now(),
        ]);

        broadcast(new MessageSent($message));

        return $message;
    }

    public function getHistory(int $roomId, int $perPage = 50, ?int $page = null)
    {
        return Message::where('room_id', $roomId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
