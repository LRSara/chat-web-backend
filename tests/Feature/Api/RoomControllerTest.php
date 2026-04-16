<?php

namespace Tests\Feature\Api;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_room_returns_201(): void
    {
        $response = $this->postJson('/api/rooms', [
            'name' => 'Sala Teste',
            'password' => 'senha123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'created_at', 'updated_at'],
            'message',
        ]);
        $this->assertDatabaseHas('rooms', ['name' => 'Sala Teste']);
    }

    public function test_create_room_validates_required_fields(): void
    {
        $response = $this->postJson('/api/rooms', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'password']);
    }

    public function test_create_room_validates_unique_name(): void
    {
        Room::create(['name' => 'Sala Existente', 'password' => bcrypt('senha')]);

        $response = $this->postJson('/api/rooms', [
            'name' => 'Sala Existente',
            'password' => 'senha123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_list_rooms_returns_collection(): void
    {
        Room::create(['name' => 'Sala 1', 'password' => bcrypt('senha')]);
        Room::create(['name' => 'Sala 2', 'password' => bcrypt('senha')]);

        $response = $this->getJson('/api/rooms');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_list_rooms_does_not_expose_password(): void
    {
        Room::create(['name' => 'Sala Segura', 'password' => bcrypt('senha123')]);

        $response = $this->getJson('/api/rooms');

        $response->assertStatus(200);
        $response->assertJsonMissing(['password' => 'senha123']);
        $this->assertStringNotContainsString('senha123', $response->getContent());
    }

    public function test_join_room_with_correct_password(): void
    {
        $room = Room::create(['name' => 'Sala Join', 'password' => bcrypt('senha123')]);

        $response = $this->postJson("/api/rooms/{$room->id}/join", [
            'password' => 'senha123',
            'nick' => 'nick1',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_sessions', [
            'room_id' => $room->id,
            'nick' => 'nick1',
        ]);
    }

    public function test_join_room_with_wrong_password(): void
    {
        $room = Room::create(['name' => 'Sala Join', 'password' => bcrypt('senha123')]);

        $response = $this->postJson("/api/rooms/{$room->id}/join", [
            'password' => 'senhaErrada',
            'nick' => 'nick1',
        ]);

        $response->assertStatus(403);
    }

    public function test_join_room_with_duplicate_nick(): void
    {
        $room = Room::create(['name' => 'Sala Join', 'password' => bcrypt('senha123')]);

        $this->postJson("/api/rooms/{$room->id}/join", [
            'password' => 'senha123',
            'nick' => 'nick1',
        ]);

        $response = $this->postJson("/api/rooms/{$room->id}/join", [
            'password' => 'senha123',
            'nick' => 'nick1',
        ]);

        $response->assertStatus(409);
    }
}
