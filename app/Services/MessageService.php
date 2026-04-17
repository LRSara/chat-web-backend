<?php

namespace App\Services;

use App\Models\Message;

class MessageService
{
    public function store(int $roomId, string $nick, string $type, ?string $content = null, ?string $filePath = null): Message
    {
        $nick = mb_strtolower(trim($nick));

        return Message::create([
            'room_id' => $roomId,
            'nick' => $nick,
            'type' => $type,
            'content' => $content,
            'file_path' => $filePath,
            'created_at' => now(),
        ]);
    }

    public function getHistory(int $roomId, int $perPage = 50, ?int $page = null)
    {
        return Message::where('room_id', $roomId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
