<?php

namespace Tests\Unit\Services;

use App\Services\UploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadServiceTest extends TestCase
{
    use RefreshDatabase;

    private UploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UploadService();
        Storage::fake('public');
    }

    public function test_store_valid_image(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(100);

        $path = $this->service->storeImage($file);

        $this->assertStringStartsWith('uploads/images/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_store_image_rejects_invalid_type(): void
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->storeImage($file);
    }

    public function test_store_image_rejects_oversized(): void
    {
        $file = UploadedFile::fake()->image('test.jpg')->size(6000);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->storeImage($file);
    }

    public function test_store_valid_audio(): void
    {
        $file = UploadedFile::fake()->create('test.webm', 100, 'audio/webm');

        $path = $this->service->storeAudio($file);

        $this->assertStringStartsWith('uploads/audio/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_store_audio_rejects_invalid_type(): void
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->storeAudio($file);
    }
}
