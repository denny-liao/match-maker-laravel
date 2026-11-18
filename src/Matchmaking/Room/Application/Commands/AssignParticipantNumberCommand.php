<?php

namespace Src\Matchmaking\Room\Application\Commands;

final readonly class AssignParticipantNumberCommand
{
    public function __construct(
        public string $code,
        public int $userId,
        public int $number,
    ) {}
}
