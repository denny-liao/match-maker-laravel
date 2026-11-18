<?php

namespace Src\Matchmaking\Room\Infrastructure\Broadcasting;

use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class RoomStateChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    /**
     * @param  array<string, mixed>  $room
     */
    public function __construct(
        public readonly array $room,
    ) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel('rooms.'.$this->room['code'])];
    }

    public function broadcastAs(): string
    {
        return 'RoomStateChanged';
    }

    /**
     * @return array{room: array<string, mixed>}
     */
    public function broadcastWith(): array
    {
        return ['room' => $this->room];
    }
}
