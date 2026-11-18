<?php

namespace Src\Matchmaking\Room\Application\Commands;

final readonly class JoinRoomCommand
{
    public function __construct(
        public string $code,
        public int $userId,
        public string $userName,
    ) {}
}
