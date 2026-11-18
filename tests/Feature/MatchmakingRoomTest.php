<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Auth\Infrastructure\EloquentModels\UserEloquentModel;
use Src\Matchmaking\Room\Application\Queries\FindRoomByCodeQuery;
use Src\Matchmaking\Room\Infrastructure\EloquentModels\RoomEloquentModel;
use Src\Matchmaking\Room\Infrastructure\EloquentModels\RoomRecordEloquentModel;
use Tests\TestCase;

class MatchmakingRoomTest extends TestCase
{
    // 測試結束的瞬間自動執行 Rollback
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_room(): void
    {
        $host = UserEloquentModel::factory()->create();

        $response = $this->actingAs($host)->post('/matchmaking/rooms', [
            'title' => '週末配對',
        ]);

        $room = RoomEloquentModel::query()->firstOrFail();

        $response->assertRedirect('/matchmaking/rooms/'.$room->code);
        $this->assertDatabaseHas('rooms', [
            'code' => $room->code,
            'host_id' => $host->id,
            'title' => '週末配對',
            'status' => 'waiting',
            'max_players' => 20,
        ]);
        $this->assertDatabaseHas('room_records', [
            'room_id' => $room->id,
            'user_id' => $host->id,
            'role' => 'host',
            'status' => 'unready',
        ]);
    }

    public function test_room_page_redirects_to_lobby_for_refresh_and_missing_room(): void
    {
        [$host, , $roomCode] = $this->createRoomWithPlayer();
        $version = hash_file('xxh128', public_path('build/manifest.json'));
        $this->assertIsString($version);

        $this->actingAs($host)
            ->get("/matchmaking/rooms/{$roomCode}")
            ->assertRedirect('/matchmaking');

        $this->actingAs($host)
            ->get('/matchmaking/rooms/unknown')
            ->assertRedirect('/matchmaking');

        $this->actingAs($host)
            ->withHeaders([
                'X-Inertia' => 'true',
                'X-Inertia-Version' => $version,
            ])
            ->get("/matchmaking/rooms/{$roomCode}")
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Matchmaking/Room');
    }

    public function test_room_waits_for_everyone_to_be_ready_before_starting(): void
    {
        [$host, $player, $roomCode] = $this->createRoomWithPlayer();

        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/ready");
        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/start");

        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'status' => 'waiting',
        ]);

        $this->actingAs($player)
            ->post("/matchmaking/rooms/{$roomCode}/ready")
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('room_records', [
            'room_id' => RoomEloquentModel::query()->where('code', $roomCode)->value('id'),
            'user_id' => $host->id,
            'status' => 'ready',
        ]);
        $this->assertDatabaseHas('room_records', [
            'room_id' => RoomEloquentModel::query()->where('code', $roomCode)->value('id'),
            'user_id' => $player->id,
            'status' => 'ready',
        ]);
        $this->actingAs($host)
            ->post("/matchmaking/rooms/{$roomCode}/start")
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'status' => 'numbering',
        ]);
    }

    public function test_room_closes_after_everyone_votes_and_only_mutual_votes_match(): void
    {
        [$host, $player, $roomCode] = $this->createRoomWithPlayer();

        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/ready");
        $this->actingAs($player)->post("/matchmaking/rooms/{$roomCode}/ready");
        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/start");

        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/number", [
            'number' => 1,
        ]);
        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'status' => 'numbering',
        ]);
        $this->actingAs($player)
            ->post("/matchmaking/rooms/{$roomCode}/number", [
                'number' => 1,
            ])
            ->assertSessionHasErrors('room');
        $this->assertDatabaseHas('room_records', [
            'room_id' => RoomEloquentModel::query()->where('code', $roomCode)->value('id'),
            'user_id' => $player->id,
            'participant_number' => null,
        ]);

        $this->actingAs($player)->post("/matchmaking/rooms/{$roomCode}/number", [
            'number' => 2,
        ]);

        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/vote", [
            'target_user_ids' => [$player->id],
        ]);

        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'status' => 'voting',
        ]);

        $this->actingAs($player)->post("/matchmaking/rooms/{$roomCode}/vote", [
            'target_user_ids' => [$host->id],
        ]);

        $room = app(FindRoomByCodeQuery::class)
            ->handle($roomCode);

        $this->assertSame('closed', $room['status']);
        $this->assertSame('completed', $room['closureReason']);
        $this->assertCount(1, $room['matches']);
        $this->assertSame(1, $room['matches'][0]['first']['number']);
        $this->assertSame($host->name, $room['matches'][0]['first']['name']);
        $this->assertSame(2, $room['matches'][0]['second']['number']);
        $this->assertSame($player->name, $room['matches'][0]['second']['name']);

        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/leave");

        $roomAfterHostLeaves = app(FindRoomByCodeQuery::class)->handle($roomCode);
        $this->assertIsArray($roomAfterHostLeaves);
        $this->assertCount(1, $roomAfterHostLeaves['participants']);
        $this->assertCount(1, $roomAfterHostLeaves['matches']);
        $this->assertSame($host->name, $roomAfterHostLeaves['matches'][0]['first']['name']);
    }

    public function test_waiting_room_stays_open_when_one_participant_remains(): void
    {
        [$host, $player, $roomCode] = $this->createRoomWithPlayer();

        $this->actingAs($host)
            ->post("/matchmaking/rooms/{$roomCode}/leave")
            ->assertRedirect('/matchmaking');

        $roomId = RoomEloquentModel::query()->where('code', $roomCode)->value('id');

        $this->assertDatabaseHas('room_records', [
            'room_id' => $roomId,
            'user_id' => $host->id,
            'status' => 'left',
        ]);
        $this->assertDatabaseHas('room_records', [
            'room_id' => $roomId,
            'user_id' => $player->id,
            'role' => 'host',
            'left_at' => null,
        ]);
        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'host_id' => $player->id,
            'status' => 'waiting',
            'closure_reason' => null,
        ]);

        $room = app(FindRoomByCodeQuery::class)->handle($roomCode);
        $this->assertIsArray($room);
        $this->assertSame('waiting', $room['status']);
        $this->assertNull($room['closureReason']);
        $this->assertCount(1, $room['participants']);

        $this->actingAs($player)
            ->post("/matchmaking/rooms/{$roomCode}/leave")
            ->assertRedirect('/matchmaking');

        $this->assertDatabaseHas('room_records', [
            'room_id' => $roomId,
            'user_id' => $player->id,
            'status' => 'left',
        ]);
        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'status' => 'closed',
            'closure_reason' => 'participants_left',
        ]);
    }

    public function test_matching_closes_when_a_leave_leaves_only_one_participant(): void
    {
        [$host, $player, $roomCode] = $this->createRoomWithPlayer();

        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/ready");
        $this->actingAs($player)->post("/matchmaking/rooms/{$roomCode}/ready");
        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/start");

        $this->actingAs($host)
            ->post("/matchmaking/rooms/{$roomCode}/leave")
            ->assertRedirect('/matchmaking');

        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'host_id' => $player->id,
            'status' => 'closed',
            'closure_reason' => 'participants_left',
        ]);
        $room = app(FindRoomByCodeQuery::class)->handle($roomCode);
        $this->assertIsArray($room);
        $this->assertSame('participants_left', $room['closureReason']);
        $this->assertCount(1, $room['participants']);
    }

    public function test_disconnecting_immediately_leaves_and_ignores_stale_connections(): void
    {
        [, $player, $roomCode] = $this->createRoomWithPlayer();
        $previousConnectionId = 'c7045998-9d98-45e8-bffa-c036002b578c';
        $currentConnectionId = 'd83cba34-4798-478a-ad09-77303de3b8c5';

        $this->actingAs($player)
            ->post("/matchmaking/rooms/{$roomCode}/reconnect", [
                'connection_id' => $previousConnectionId,
            ])
            ->assertNoContent();
        $this->actingAs($player)
            ->post("/matchmaking/rooms/{$roomCode}/reconnect", [
                'connection_id' => $currentConnectionId,
            ])
            ->assertNoContent();
        $this->actingAs($player)
            ->post("/matchmaking/rooms/{$roomCode}/disconnect", [
                'connection_id' => $previousConnectionId,
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('room_records', [
            'room_id' => RoomEloquentModel::query()->where('code', $roomCode)->value('id'),
            'user_id' => $player->id,
            'left_at' => null,
        ]);

        $this->actingAs($player)
            ->post("/matchmaking/rooms/{$roomCode}/disconnect", [
                'connection_id' => $currentConnectionId,
            ])
            ->assertNoContent();

        $this->assertDatabaseMissing('room_records', [
            'room_id' => RoomEloquentModel::query()->where('code', $roomCode)->value('id'),
            'user_id' => $player->id,
            'left_at' => null,
        ]);
    }

    public function test_last_participant_disconnect_closes_the_room(): void
    {
        $host = UserEloquentModel::factory()->create();
        $this->actingAs($host)->post('/matchmaking/rooms', [
            'title' => '離開時自動關閉',
        ]);
        $room = RoomEloquentModel::query()->firstOrFail();
        $connectionId = 'c7045998-9d98-45e8-bffa-c036002b578c';

        $this->actingAs($host)
            ->post("/matchmaking/rooms/{$room->code}/reconnect", [
                'connection_id' => $connectionId,
            ])
            ->assertNoContent();
        $this->actingAs($host)
            ->post("/matchmaking/rooms/{$room->code}/disconnect", [
                'connection_id' => $connectionId,
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'closed',
            'closure_reason' => 'participants_left',
        ]);
        $this->assertDatabaseHas('room_records', [
            'room_id' => $room->id,
            'user_id' => $host->id,
            'status' => 'left',
        ]);
    }

    public function test_room_rejects_participants_above_twenty(): void
    {
        $host = UserEloquentModel::factory()->create();
        $this->actingAs($host)->post('/matchmaking/rooms', [
            'title' => '最多二十人',
        ]);
        $roomCode = RoomEloquentModel::query()->value('code');
        $this->assertIsString($roomCode);

        for ($i = 1; $i < 20; $i++) {
            $participant = UserEloquentModel::factory()->create();
            $this->actingAs($participant)
                ->post("/matchmaking/rooms/{$roomCode}/join")
                ->assertRedirect("/matchmaking/rooms/{$roomCode}");
        }

        $extraParticipant = UserEloquentModel::factory()->create();
        $this->actingAs($extraParticipant)
            ->post("/matchmaking/rooms/{$roomCode}/join")
            ->assertSessionHasErrors('room');

        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'max_players' => 20,
        ]);
        $this->assertSame(
            20,
            RoomEloquentModel::query()
                ->where('code', $roomCode)
                ->firstOrFail()
                ->records()
                ->whereNull('left_at')
                ->count(),
        );
    }

    public function test_host_ownership_passes_in_join_order_until_one_participant_remains(): void
    {
        [$host, $firstPlayer, $roomCode] = $this->createRoomWithPlayer();
        $secondPlayer = UserEloquentModel::factory()->create();

        $this->actingAs($secondPlayer)
            ->post("/matchmaking/rooms/{$roomCode}/join")
            ->assertSessionHasNoErrors();

        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/leave");

        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'host_id' => $firstPlayer->id,
            'status' => 'waiting',
        ]);
        $this->assertDatabaseHas('room_records', [
            'user_id' => $host->id,
            'role' => 'player',
            'status' => 'left',
        ]);
        $this->assertDatabaseHas('room_records', [
            'user_id' => $firstPlayer->id,
            'role' => 'host',
            'left_at' => null,
        ]);

        $this->actingAs($firstPlayer)
            ->post("/matchmaking/rooms/{$roomCode}/leave");

        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'host_id' => $secondPlayer->id,
            'status' => 'waiting',
            'closure_reason' => null,
        ]);

        $room = app(FindRoomByCodeQuery::class)->handle($roomCode);
        $this->assertIsArray($room);
        $this->assertCount(1, $room['participants']);
        $this->assertSame($secondPlayer->id, $room['participants'][0]['userId']);

        $this->actingAs($secondPlayer)
            ->post("/matchmaking/rooms/{$roomCode}/leave");

        $this->assertDatabaseHas('rooms', [
            'code' => $roomCode,
            'status' => 'closed',
            'closure_reason' => 'participants_left',
        ]);
    }

    public function test_leaving_participant_is_removed_from_remaining_votes_and_matches(): void
    {
        [$host, $firstPlayer, $roomCode] = $this->createRoomWithPlayer();
        $leavingPlayer = UserEloquentModel::factory()->create();

        $this->actingAs($leavingPlayer)->post("/matchmaking/rooms/{$roomCode}/join");
        foreach ([$host, $firstPlayer, $leavingPlayer] as $participant) {
            $this->actingAs($participant)->post("/matchmaking/rooms/{$roomCode}/ready");
        }
        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/start");

        foreach ([[$host, 1], [$firstPlayer, 2], [$leavingPlayer, 3]] as [$participant, $number]) {
            $this->actingAs($participant)->post("/matchmaking/rooms/{$roomCode}/number", [
                'number' => $number,
            ]);
        }

        $this->actingAs($host)->post("/matchmaking/rooms/{$roomCode}/vote", [
            'target_user_ids' => [$firstPlayer->id, $leavingPlayer->id],
        ]);
        $this->actingAs($firstPlayer)->post("/matchmaking/rooms/{$roomCode}/vote", [
            'target_user_ids' => [],
        ]);
        $this->actingAs($leavingPlayer)->post("/matchmaking/rooms/{$roomCode}/leave");

        $room = app(FindRoomByCodeQuery::class)->handle($roomCode);
        $this->assertIsArray($room);
        $this->assertSame('closed', $room['status']);
        $this->assertSame('completed', $room['closureReason']);
        $this->assertCount(2, $room['participants']);
        $hostRecord = RoomRecordEloquentModel::query()
            ->where('room_id', RoomEloquentModel::query()->where('code', $roomCode)->value('id'))
            ->where('user_id', $host->id)
            ->firstOrFail();
        $this->assertSame([$firstPlayer->id], $hostRecord->voted_user_ids);
        $this->assertCount(0, $room['matches']);
    }

    /**
     * @return array{UserEloquentModel, UserEloquentModel, string}
     */
    private function createRoomWithPlayer(): array
    {
        $host = UserEloquentModel::factory()->create();
        $player = UserEloquentModel::factory()->create();

        $this->actingAs($host)->post('/matchmaking/rooms', [
            'title' => '測試房間',
        ]);

        $roomCode = RoomEloquentModel::query()->value('code');
        $this->assertIsString($roomCode);

        $joinResponse = $this->actingAs($player)
            ->post("/matchmaking/rooms/{$roomCode}/join")
            ->assertSessionHasNoErrors();
        $joinResponse->assertRedirect("/matchmaking/rooms/{$roomCode}");
        $this->assertDatabaseHas('room_records', [
            'room_id' => RoomEloquentModel::query()->where('code', $roomCode)->value('id'),
            'user_id' => $player->id,
            'role' => 'player',
        ]);

        return [$host, $player, $roomCode];
    }
}
