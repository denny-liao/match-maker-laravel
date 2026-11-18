<?php

namespace Src\Matchmaking\Room\Application\CommandHandlers;

use Src\Matchmaking\Room\Application\Commands\LeaveRoomCommand;
use Src\Matchmaking\Room\Application\DTOs\RoomData;
use Src\Matchmaking\Room\Domain\Entities\Room;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomLobbyUpdated;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomStateChanged;

final readonly class LeaveRoomHandler
{
    public function __construct(
        private RoomRepositoryInterface $rooms,
    ) {}

    public function handle(LeaveRoomCommand $command): Room
    {
        $room = $this->rooms->mutate($command->code, function (Room $room) use ($command): void {
            $room->leave($command->userId);
        });

        $roomData = RoomData::fromRoom($room);

        event(new RoomStateChanged($roomData));
        event(new RoomLobbyUpdated($roomData));

        return $room;
    }
}
