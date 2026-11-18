<?php

namespace Src\Matchmaking\Room\Application\Commands;

final readonly class StartMatchingCommand
{
    public function __construct(
        public string $code,
        public int $userId,
    ) {}
}
