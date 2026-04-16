<?php

namespace Tests\Unit\Services;

use App\Services\MessageService;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    private MessageService $service;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MessageService();
        $this->room = Room::create(['name' => 'Sala Mensagens', 'password' => bcrypt('senha')]);
    }

    public function test_store_text_message(): void
    {
        $message = $this->service->store($this->room->id, 'nick1', 'text', 'Olá mundo');

        $this->assertDatabaseHas('messages', [
            'room_id' => $this->room->id,
            'nick' => 'nick1',
            'type' => 'text',
            'content' => 'Olá mundo',
        ]);
    }

    public function test_store_image_message(): void
    {
        $message = $this->service->store($this->room->id, 'nick1', 'image', null, 'uploads/images/test.jpg');

        $this->assertDatabaseHas('messages', [
            'room_id' => $this->room->id,
            'nick' => 'nick1',
            'type' => 'image',
            'file_path' => 'uploads/images/test.jpg',
        ]);
    }

    public function test_store_audio_message(): void
    {
        $message = $this->service->store($this->room->id, 'nick1', 'audio', null, 'uploads/audio/test.webm');

        $this->assertDatabaseHas('messages', [
            'room_id' => $this->room->id,
            'nick' => 'nick1',
            'type' => 'audio',
            'file_path' => 'uploads/audio/test.webm',
        ]);
    }

    public function test_get_history_returns_paginated(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $this->service->store($this->room->id, 'nick1', 'text', "Mensagem $i");
        }

        $result = $this->service->getHistory($this->room->id, 50);

        $this->assertCount(50, $result->items());
        $this->assertTrue($result->hasMorePages());
    }

    public function test_get_history_returns_correct_order(): void
    {
        $this->service->store($this->room->id, 'nick1', 'text', 'Primeira');
        sleep(1);
        $this->service->store($this->room->id, 'nick1', 'text', 'Segunda');

        $result = $this->service->getHistory($this->room->id, 50);
        $items = $result->items();

        $this->assertEquals('Segunda', $items[0]->content);
        $this->assertEquals('Primeira', $items[1]->content);
    }
}
