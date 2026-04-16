<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_upload_valid_image(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(100);

        $response = $this->postJson('/api/upload/image', [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['path', 'url'],
            'message',
        ]);
    }

    public function test_upload_invalid_image_type(): void
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/upload/image', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
    }

    public function test_upload_oversized_image(): void
    {
        $file = UploadedFile::fake()->image('test.jpg')->size(6000);

        $response = $this->postJson('/api/upload/image', [
            'file' => $file,
        ]);

        $response->assertStatus(413);
    }

    public function test_upload_valid_audio(): void
    {
        $file = UploadedFile::fake()->create('test.webm', 100, 'audio/webm');

        $response = $this->postJson('/api/upload/audio', [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['path', 'url'],
            'message',
        ]);
    }

    public function test_upload_invalid_audio_type(): void
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->postJson('/api/upload/audio', [
            'file' => $file,
        ]);

        $response->assertStatus(422);
    }
}
