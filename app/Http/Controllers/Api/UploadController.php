<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function __construct(
        private UploadService $uploadService,
    ) {}

    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $path = $this->uploadService->storeImage($request->file('file'));

        return response()->json([
            'data' => [
                'path' => $path,
                'url' => asset('storage/' . $path),
            ],
            'message' => 'Imagem enviada com sucesso.',
        ], 201);
    }

    public function audio(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $path = $this->uploadService->storeAudio($request->file('file'));

        return response()->json([
            'data' => [
                'path' => $path,
                'url' => asset('storage/' . $path),
            ],
            'message' => 'Áudio enviado com sucesso.',
        ], 201);
    }
}
