<?php

namespace Src\Matchmaking\Room\Application\CommandHandlers;

use Src\Matchmaking\Room\Application\Commands\JoinRoomCommand;
use Src\Matchmaking\Room\Application\DTOs\RoomData;
use Src\Matchmaking\Room\Domain\Entities\Participant;
use Src\Matchmaking\Room\Domain\Entities\Room;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomLobbyUpdated;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomStateChanged;

final readonly class JoinRoomHandler
{
    public function __construct(
        private RoomRepositoryInterface $rooms,
    ) {}

    public function handle(JoinRoomCommand $command): Room
    {
        $room = $this->rooms->mutate($command->code, function (Room $room) use ($command): void {
            $room->join(new Participant(
                recordId: null,
                userId: $command->userId,
                name: $command->userName,
            ));
        });

        event(new RoomStateChanged(RoomData::fromRoom($room)));
        event(new RoomLobbyUpdated(RoomData::fromRoom($room)));

        return $room;
    }
}
