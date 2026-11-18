<?php

namespace Src\Matchmaking\Room\Application\CommandHandlers;

use Src\Matchmaking\Room\Application\Commands\StartMatchingCommand;
use Src\Matchmaking\Room\Application\DTOs\RoomData;
use Src\Matchmaking\Room\Domain\Entities\Room;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomStateChanged;

final readonly class StartMatchingHandler
{
    public function __construct(
        private RoomRepositoryInterface $rooms,
    ) {}

    public function handle(StartMatchingCommand $command): Room
    {
        $room = $this->rooms->mutate($command->code, function (Room $room) use ($command): void {
            $room->startMatching($command->userId);
        });

        event(new RoomStateChanged(RoomData::fromRoom($room)));

        return $room;
    }
}
