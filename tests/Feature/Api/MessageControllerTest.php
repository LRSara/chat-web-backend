<?php

namespace Tests\Feature\Api;

use App\Models\Room;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->room = Room::create(['name' => 'Sala Mensagens', 'password' => bcrypt('senha')]);
    }

    public function test_get_messages_returns_paginated(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            Message::create([
                'room_id' => $this->room->id,
                'nick' => 'nick1',
                'type' => 'text',
                'content' => "Mensagem $i",
            ]);
        }

        $response = $this->getJson("/api/rooms/{$this->room->id}/messages");

        $response->assertStatus(200);
        $response->assertJsonCount(50, 'data');
        $response->assertJsonPath('meta.has_more', true);
    }

    public function test_get_messages_returns_correct_order(): void
    {
        Message::create([
            'room_id' => $this->room->id,
            'nick' => 'nick1',
            'type' => 'text',
            'content' => 'Primeira',
            'created_at' => now()->subMinute(),
        ]);

        Message::create([
            'room_id' => $this->room->id,
            'nick' => 'nick1',
            'type' => 'text',
            'content' => 'Segunda',
        ]);

        $response = $this->getJson("/api/rooms/{$this->room->id}/messages");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Segunda', $data[0]['content']);
        $this->assertEquals('Primeira', $data[1]['content']);
    }

    public function test_send_text_message(): void
    {
        $response = $this->postJson("/api/rooms/{$this->room->id}/messages", [
            'nick' => 'nick1',
            'type' => 'text',
            'content' => 'Olá mundo',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'room_id' => $this->room->id,
            'nick' => 'nick1',
            'type' => 'text',
            'content' => 'Olá mundo',
        ]);
    }

    public function test_send_image_message(): void
    {
        $response = $this->postJson("/api/rooms/{$this->room->id}/messages", [
            'nick' => 'nick1',
            'type' => 'image',
            'file' => \Illuminate\Http\UploadedFile::fake()->image('test.jpg'),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'room_id' => $this->room->id,
            'nick' => 'nick1',
            'type' => 'image',
        ]);
    }

    public function test_send_audio_message(): void
    {
        $response = $this->postJson("/api/rooms/{$this->room->id}/messages", [
            'nick' => 'nick1',
            'type' => 'audio',
            'file' => \Illuminate\Http\UploadedFile::fake()->create('test.webm', 100, 'audio/webm'),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'room_id' => $this->room->id,
            'nick' => 'nick1',
            'type' => 'audio',
        ]);
    }
}
