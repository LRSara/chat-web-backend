<?php

namespace Tests\Unit\Services;

use App\Services\RoomService;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomServiceTest extends TestCase
{
    use RefreshDatabase;

    private RoomService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoomService();
    }

    public function test_create_room_hashes_password(): void
    {
        $room = $this->service->createRoom('Sala Teste', 'senha123');

        $this->assertDatabaseHas('rooms', [
            'name' => 'Sala Teste',
        ]);

        $this->assertNotEquals('senha123', $room->password);
        $this->assertTrue(password_verify('senha123', $room->password));
    }

    public function test_create_room_with_unique_name(): void
    {
        $this->service->createRoom('Sala Unica', 'senha123');

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->service->createRoom('Sala Unica', 'outraSenha');
    }

    public function test_join_room_with_correct_password(): void
    {
        $room = $this->service->createRoom('Sala Join', 'senha123');

        $session = $this->service->joinRoom($room->id, 'senha123', 'nick1');

        $this->assertEquals('nick1', $session->nick);
        $this->assertEquals($room->id, $session->room_id);
        $this->assertNull($session->disconnected_at);
    }

    public function test_join_room_with_wrong_password(): void
    {
        $room = $this->service->createRoom('Sala Join', 'senha123');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->joinRoom($room->id, 'senhaErrada', 'nick1');
    }

    public function test_join_room_with_duplicate_nick_same_room(): void
    {
        $room = $this->service->createRoom('Sala Join', 'senha123');
        $this->service->joinRoom($room->id, 'senha123', 'nick1');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->joinRoom($room->id, 'senha123', 'nick1');
    }

    public function test_join_room_with_same_nick_different_room(): void
    {
        $room1 = $this->service->createRoom('Sala 1', 'senha123');
        $room2 = $this->service->createRoom('Sala 2', 'senha123');

        $this->service->joinRoom($room1->id, 'senha123', 'nick1');
        $session = $this->service->joinRoom($room2->id, 'senha123', 'nick1');

        $this->assertEquals('nick1', $session->nick);
        $this->assertEquals($room2->id, $session->room_id);
    }

    public function test_leave_room(): void
    {
        $room = $this->service->createRoom('Sala Leave', 'senha123');
        $session = $this->service->joinRoom($room->id, 'senha123', 'nick1');

        $this->service->leaveRoom($room->id, 'nick1');

        $session->refresh();
        $this->assertNotNull($session->disconnected_at);
    }
}
