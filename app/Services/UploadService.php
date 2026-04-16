<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    private const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const IMAGE_MAX_SIZE = 5 * 1024 * 1024;

    private const AUDIO_TYPES = ['audio/webm', 'audio/mpeg', 'audio/ogg', 'audio/mp4'];
    private const AUDIO_MAX_SIZE = 10 * 1024 * 1024;

    public function storeImage(UploadedFile $file): string
    {
        if (!in_array($file->getMimeType(), self::IMAGE_TYPES)) {
            abort(422, 'Formato de imagem não suportado.');
        }

        if ($file->getSize() > self::IMAGE_MAX_SIZE) {
            abort(413, 'Imagem excede o limite de 5MB.');
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/images', $filename, 'public');

        return $path;
    }

    public function storeAudio(UploadedFile $file): string
    {
        if (!$this->isValidAudioType($file)) {
            abort(422, 'Formato de áudio não suportado.');
        }

        if ($file->getSize() > self::AUDIO_MAX_SIZE) {
            abort(413, 'Áudio excede o limite de 10MB.');
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/audio', $filename, 'public');

        return $path;
    }

    private function isValidAudioType(UploadedFile $file): bool
    {
        $detected = $file->getMimeType();
        if (in_array($detected, self::AUDIO_TYPES)) {
            return true;
        }

        $clientMime = $file->getClientMimeType();
        if (in_array($clientMime, self::AUDIO_TYPES)) {
            return true;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $extensionMap = [
            'webm' => 'audio/webm',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'oga' => 'audio/ogg',
            'mp4' => 'audio/mp4',
            'm4a' => 'audio/mp4',
        ];

        return isset($extensionMap[$extension]);
    }
}
