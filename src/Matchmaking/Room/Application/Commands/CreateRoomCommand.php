<?php

namespace Src\Matchmaking\Room\Application\Commands;

final readonly class CreateRoomCommand
{
    public function __construct(
        public int $userId,
        public string $userName,
        public string $title,
    ) {}
}
