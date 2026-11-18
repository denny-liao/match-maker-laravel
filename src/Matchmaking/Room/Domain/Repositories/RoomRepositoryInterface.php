<?php

namespace Src\Matchmaking\Room\Domain\Repositories;

use Closure;
use Src\Matchmaking\Room\Domain\Entities\Room;

interface RoomRepositoryInterface
{
    public function create(Room $room): Room;

    /**
     * @return list<Room>
     */
    public function listOpenRooms(): array;

    public function findByCode(string $code): ?Room;

    public function mutate(string $code, Closure $operation): Room;
}
