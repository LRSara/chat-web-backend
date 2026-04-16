<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\Message;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_full_chat_flow(): void
    {
        // 1. Criar sala
        $response = $this->postJson('/api/rooms', [
            'name' => 'Sala E2E',
            'password' => 'senha123',
        ]);
        $response->assertStatus(201);
        $roomId = $response->json('data.id');

        // 2. Entrar na sala
        $response = $this->postJson("/api/rooms/{$roomId}/join", [
            'password' => 'senha123',
            'nick' => 'usuario1',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('user_sessions', [
            'room_id' => $roomId,
            'nick' => 'usuario1',
        ]);

        // 3. Enviar mensagem de texto
        $response = $this->postJson("/api/rooms/{$roomId}/messages", [
            'nick' => 'usuario1',
            'type' => 'text',
            'content' => 'Olá mundo',
        ]);
        $response->assertStatus(201);

        // 4. Enviar mensagem de imagem
        $response = $this->postJson("/api/rooms/{$roomId}/messages", [
            'nick' => 'usuario1',
            'type' => 'image',
            'file' => UploadedFile::fake()->image('foto.jpg'),
        ]);
        $response->assertStatus(201);

        // 5. Enviar mensagem de áudio
        $response = $this->postJson("/api/rooms/{$roomId}/messages", [
            'nick' => 'usuario1',
            'type' => 'audio',
            'file' => UploadedFile::fake()->create('audio.webm', 100, 'audio/webm'),
        ]);
        $response->assertStatus(201);

        // 6. Sair da sala
        $response = $this->postJson("/api/rooms/{$roomId}/leave/usuario1");
        $response->assertStatus(200);
        $session = UserSession::where('room_id', $roomId)->where('nick', 'usuario1')->first();
        $this->assertNotNull($session->disconnected_at);

        // 7. Entrar novamente
        $response = $this->postJson("/api/rooms/{$roomId}/join", [
            'password' => 'senha123',
            'nick' => 'usuario1',
        ]);
        $response->assertStatus(200);

        // 8. Verificar histórico íntegro
        $response = $this->getJson("/api/rooms/{$roomId}/messages");
        $response->assertStatus(200);
        $messages = $response->json('data');

        $this->assertCount(3, $messages);
        $this->assertEquals('audio', $messages[0]['type']);
        $this->assertEquals('image', $messages[1]['type']);
        $this->assertEquals('text', $messages[2]['type']);
        $this->assertEquals('Olá mundo', $messages[2]['content']);
    }
}
