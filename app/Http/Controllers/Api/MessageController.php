<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MessageService;
use App\Services\UploadService;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private UploadService $uploadService,
    ) {}

    public function index(Request $request, int $roomId): JsonResponse
    {
        $room = Room::findOrFail($roomId);

        $messages = $this->messageService->getHistory(
            $roomId,
            $request->integer('per_page', 50),
            $request->integer('page'),
        );

        return response()->json([
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'has_more' => $messages->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request, int $roomId): JsonResponse
    {
        $room = Room::findOrFail($roomId);

        $request->validate([
            'nick' => 'required|string|min:3|max:50',
            'type' => 'required|in:text,image,audio',
            'content' => 'required_if:type,text|nullable|string',
            'file' => 'required_if:type,image|required_if:type,audio|nullable|file',
        ]);

        $nick = $request->input('nick');
        $type = $request->input('type');
        $content = $request->input('content');
        $filePath = null;

        if ($type === 'image' && $request->hasFile('file')) {
            $filePath = $this->uploadService->storeImage($request->file('file'));
        } elseif ($type === 'audio' && $request->hasFile('file')) {
            $filePath = $this->uploadService->storeAudio($request->file('file'));
        }

        $message = $this->messageService->store($roomId, $nick, $type, $content, $filePath);

        return response()->json([
            'data' => $message,
            'message' => 'Mensagem enviada com sucesso.',
        ], 201);
    }
}
