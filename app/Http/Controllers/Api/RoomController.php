<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\JoinRoomRequest;
use App\Services\RoomService;
use App\Events\UserJoined;
use App\Events\UserLeft;
use App\Models\Room;
use Illuminate\Http\JsonResponse;

class RoomController extends Controller
{
    public function __construct(
        private RoomService $roomService,
    ) {}

    public function store(CreateRoomRequest $request): JsonResponse
    {
        $room = $this->roomService->createRoom(
            $request->validated('name'),
            $request->validated('password'),
        );

        return response()->json([
            'data' => $room,
            'message' => 'Sala criada com sucesso.',
        ], 201);
    }

    public function index(): JsonResponse
    {
        $rooms = $this->roomService->listRooms();

        return response()->json([
            'data' => $rooms,
        ]);
    }

    public function join(JoinRoomRequest $request, int $id): JsonResponse
    {
        $session = $this->roomService->joinRoom(
            $id,
            $request->validated('password'),
            $request->validated('nick'),
        );

        $room = Room::findOrFail($id);
        $onlineUsers = $room->onlineUsers()->pluck('nick')->toArray();
        broadcast(new UserJoined($id, $session->nick, $onlineUsers));

        return response()->json([
            'data' => $session,
            'message' => 'Entrou na sala com sucesso.',
        ]);
    }

    public function leave(int $id, string $nick): JsonResponse
    {
        $this->roomService->leaveRoom($id, $nick);

        $room = Room::findOrFail($id);
        $onlineUsers = $room->onlineUsers()->pluck('nick')->toArray();
        broadcast(new UserLeft($id, $nick, $onlineUsers));

        return response()->json([
            'message' => 'Saiu da sala com sucesso.',
        ]);
    }

    public function online(int $id): JsonResponse
    {
        $room = \App\Models\Room::findOrFail($id);
        $nicks = $room->onlineUsers()->pluck('nick')->toArray();

        return response()->json([
            'data' => $nicks,
        ]);
    }
}
