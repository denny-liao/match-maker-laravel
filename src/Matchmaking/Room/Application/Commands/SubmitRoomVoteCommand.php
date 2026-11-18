<?php

namespace Src\Matchmaking\Room\Application\Commands;

final readonly class SubmitRoomVoteCommand
{
    /**
     * @param  list<int>  $targetUserIds
     */
    public function __construct(
        public string $code,
        public int $userId,
        public array $targetUserIds,
    ) {}
}
