<?php

namespace Src\Matchmaking\Room\Infrastructure\Broadcasting;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class RoomLobbyUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    /**
     * @param  array<string, mixed>  $room
     */
    public function __construct(
        public readonly array $room,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('matchmaking.lobby')];
    }

    public function broadcastAs(): string
    {
        return 'RoomLobbyUpdated';
    }

    /**
     * @return array{room: array<string, mixed>}
     */
    public function broadcastWith(): array
    {
        return ['room' => $this->room];
    }
}
