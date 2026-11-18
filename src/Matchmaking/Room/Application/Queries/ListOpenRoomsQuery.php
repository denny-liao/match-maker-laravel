<?php

namespace Src\Matchmaking\Room\Application\Queries;

use Src\Matchmaking\Room\Application\DTOs\RoomData;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;

final readonly class ListOpenRoomsQuery
{
    public function __construct(
        private RoomRepositoryInterface $rooms,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(): array
    {
        return array_map(
            static fn ($room): array => RoomData::fromRoom($room),
            $this->rooms->listOpenRooms(),
        );
    }
}
