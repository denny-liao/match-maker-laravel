<?php

namespace Src\Matchmaking\Room\Application\Queries;

use Src\Matchmaking\Room\Application\DTOs\RoomData;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;

final readonly class FindRoomByCodeQuery
{
    public function __construct(
        private RoomRepositoryInterface $rooms,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function handle(string $code): ?array
    {
        $room = $this->rooms->findByCode($code);

        return $room === null ? null : RoomData::fromRoom($room);
    }
}
