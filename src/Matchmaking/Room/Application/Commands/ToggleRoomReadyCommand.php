<?php

namespace Src\Matchmaking\Room\Application\Commands;

final readonly class ToggleRoomReadyCommand
{
    public function __construct(
        public string $code,
        public int $userId,
    ) {}
}
