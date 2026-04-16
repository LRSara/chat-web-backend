<?php

namespace App\Services;

use App\Models\Room;
use App\Models\UserSession;
use App\Events\UserJoined;
use App\Events\UserLeft;
use Illuminate\Support\Facades\Hash;

class RoomService
{
    public function createRoom(string $name, string $password): Room
    {
        return Room::create([
            'name' => $name,
            'password' => Hash::make($password),
        ]);
    }

    public function listRooms()
    {
        return Room::withCount(['userSessions as online_users_count' => function ($query) {
            $query->online();
        }])->orderBy('created_at', 'desc')->get();
    }

    public function joinRoom(int $roomId, string $password, string $nick): UserSession
    {
        $room = Room::findOrFail($roomId);

        if (!Hash::check($password, $room->password)) {
            abort(403, 'Senha incorreta.');
        }

        $existingSession = UserSession::where('room_id', $roomId)
            ->where('nick', $nick)
            ->whereNull('disconnected_at')
            ->first();

        if ($existingSession) {
            abort(409, 'Usuário online já existente com esse nick, tente outro.');
        }

        $session = UserSession::create([
            'room_id' => $roomId,
            'nick' => $nick,
            'connected_at' => now(),
        ]);

        $onlineUsers = $room->onlineUsers()->pluck('nick')->toArray();
        broadcast(new UserJoined($roomId, $nick, $onlineUsers));

        return $session;
    }

    public function leaveRoom(int $roomId, string $nick): void
    {
        $session = UserSession::where('room_id', $roomId)
            ->where('nick', $nick)
            ->whereNull('disconnected_at')
            ->firstOrFail();

        $session->update(['disconnected_at' => now()]);

        $room = Room::findOrFail($roomId);
        $onlineUsers = $room->onlineUsers()->pluck('nick')->toArray();
        broadcast(new UserLeft($roomId, $nick, $onlineUsers));
    }
}
