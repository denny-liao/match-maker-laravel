<?php

namespace Src\Matchmaking\Room\Domain\Entities;

use DateTimeImmutable;

final class Participant
{
    /**
     * @param  list<int>|null  $votedUserIds
     */
    public function __construct(
        public ?int $recordId,
        public readonly int $userId,
        public readonly string $name,
        public string $role = 'player',
        public string $status = 'unready',
        public ?int $participantNumber = null,
        public ?array $votedUserIds = null,
        public ?DateTimeImmutable $leftAt = null,
    ) {}

    public function hasLeft(): bool
    {
        return $this->leftAt !== null;
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function hasVoted(): bool
    {
        return $this->votedUserIds !== null;
    }
}
