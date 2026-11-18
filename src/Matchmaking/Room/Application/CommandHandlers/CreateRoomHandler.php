<?php

namespace Src\Matchmaking\Room\Application\CommandHandlers;

use Illuminate\Support\Str;
use Src\Matchmaking\Room\Application\Commands\CreateRoomCommand;
use Src\Matchmaking\Room\Application\DTOs\RoomData;
use Src\Matchmaking\Room\Domain\Entities\Room;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomLobbyUpdated;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomStateChanged;

final readonly class CreateRoomHandler
{
    public function __construct(
        private RoomRepositoryInterface $rooms,
    ) {}

    public function handle(CreateRoomCommand $command): Room
    {
        $room = $this->rooms->create(Room::create(
            code: Str::upper(Str::random(8)),
            hostId: $command->userId,
            hostName: $command->userName,
            title: $command->title,
        ));

        event(new RoomStateChanged(RoomData::fromRoom($room)));
        event(new RoomLobbyUpdated(RoomData::fromRoom($room)));

        return $room;
    }
}
