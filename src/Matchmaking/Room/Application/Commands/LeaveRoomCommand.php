<?php

namespace Src\Matchmaking\Room\Application\Commands;

final readonly class LeaveRoomCommand
{
    public function __construct(
        public string $code,
        public int $userId,
    ) {}
}
