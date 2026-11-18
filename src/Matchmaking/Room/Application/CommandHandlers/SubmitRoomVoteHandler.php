<?php

namespace Src\Matchmaking\Room\Application\CommandHandlers;

use Src\Matchmaking\Room\Application\Commands\SubmitRoomVoteCommand;
use Src\Matchmaking\Room\Application\DTOs\RoomData;
use Src\Matchmaking\Room\Domain\Entities\Room;
use Src\Matchmaking\Room\Domain\Enums\RoomStatus;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomLobbyUpdated;
use Src\Matchmaking\Room\Infrastructure\Broadcasting\RoomStateChanged;

final readonly class SubmitRoomVoteHandler
{
    public function __construct(
        private RoomRepositoryInterface $rooms,
    ) {}

    public function handle(SubmitRoomVoteCommand $command): Room
    {
        $room = $this->rooms->mutate($command->code, function (Room $room) use ($command): void {
            $room->vote($command->userId, $command->targetUserIds);
        });

        event(new RoomStateChanged(RoomData::fromRoom($room)));

        if ($room->status === RoomStatus::Closed) {
            event(new RoomLobbyUpdated(RoomData::fromRoom($room)));
        }

        return $room;
    }
}
