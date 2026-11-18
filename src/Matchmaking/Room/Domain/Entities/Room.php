<?php

namespace Src\Matchmaking\Room\Domain\Entities;

use DateTimeImmutable;
use Src\Common\Domain\AggregateRoot;
use Src\Matchmaking\Room\Domain\Enums\RoomClosureReason;
use Src\Matchmaking\Room\Domain\Enums\RoomStatus;
use Src\Matchmaking\Room\Domain\Exceptions\RoomRuleException;

final class Room extends AggregateRoot
{
    public const MAX_PLAYERS = 20;

    /**
     * @param  list<Participant>  $participants
     */
    public function __construct(
        public ?int $id,
        public readonly string $code,
        public int $hostId,
        public string $title,
        public RoomStatus $status,
        public int $maxPlayers,
        public array $participants,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $closedAt = null,
        public ?RoomClosureReason $closureReason = null,
    ) {}

    public static function create(
        string $code,
        int $hostId,
        string $hostName,
        string $title,
        int $maxPlayers = self::MAX_PLAYERS,
    ): self {
        if ($maxPlayers < 2 || $maxPlayers > self::MAX_PLAYERS) {
            throw new RoomRuleException('房間人數上限必須介於 2 至 20 人。');
        }

        return new self(
            id: null,
            code: $code,
            hostId: $hostId,
            title: $title,
            status: RoomStatus::Waiting,
            maxPlayers: $maxPlayers,
            participants: [
                new Participant(
                    recordId: null,
                    userId: $hostId,
                    name: $hostName,
                    role: 'host',
                ),
            ],
        );
    }
    // 加入參與者
    public function join(Participant $participant): void
    {
        $this->ensureStatus(RoomStatus::Waiting);

        if ($this->participant($participant->userId) !== null) {
            return;
        }

        if (count($this->activeParticipants()) >= min($this->maxPlayers, self::MAX_PLAYERS)) {
            throw new RoomRuleException('房間人數已滿。');
        }

        $this->participants[] = $participant;
    }

    public function leave(int $userId): void
    {
        // 目前要離開的參與者
        $participant = $this->participant($userId);

        // 參與者已離開
        if ($participant === null) {
            foreach ($this->participants as $previousParticipant) {
                if ($previousParticipant->userId === $userId && $previousParticipant->hasLeft()) {
                    return;
                }
            }

            throw new RoomRuleException('你不是此房間的參與者。');
        }

        $wasMatchingStarted = in_array(
            $this->status,
            [RoomStatus::Numbering, RoomStatus::Voting],
            true,
        );
        $participant->leftAt = new DateTimeImmutable;
        $participant->status = 'left';
        $participant->role = 'player';

        $activeParticipants = $this->activeParticipants();
        // 配對進行中
        if ($wasMatchingStarted) {
            // 離開的參與者
            $participant->votedUserIds = null;

            foreach ($activeParticipants as $activeParticipant) {
                if ($activeParticipant->votedUserIds !== null) {
                    // 過濾掉離開的參與者
                    $activeParticipant->votedUserIds = array_values(array_filter(
                        $activeParticipant->votedUserIds,
                        static fn (int $targetUserId): bool => $targetUserId !== $userId,
                    ));
                }
            }
        }
        // 沒人、配對進行中並且剩一人 => 自動關閉房間
        if (
            $activeParticipants === []
            || ($wasMatchingStarted && count($activeParticipants) === 1)
        ) {
            $this->status = RoomStatus::Closed;
            $this->closedAt ??= new DateTimeImmutable;
            $this->closureReason ??= RoomClosureReason::ParticipantsLeft;
        }
        // 房主離開，轉移房主權限給下一參與者
        if ($participant->userId === $this->hostId) {
            if ($activeParticipants !== []) {
                $newHost = $activeParticipants[0];
                $newHost->role = 'host';
                $this->hostId = $newHost->userId;
            }
        }
        // 房間已關閉
        if (! $wasMatchingStarted || $this->status === RoomStatus::Closed) {
            return;
        }
        // 編號填寫進行中 並且 剩下的人都已填編號 (離開者沒有填編號) => 進入投票
        if (
            $this->status === RoomStatus::Numbering
            && $this->allParticipantsHaveNumber($activeParticipants)
        ) {
            $this->status = RoomStatus::Voting;
        }
        // 投票進行中 並且 剩下的人均已投票
        if (
            $this->status === RoomStatus::Voting
            && $this->allParticipantsHaveVoted($activeParticipants)
        ) {
            $this->status = RoomStatus::Closed;
            $this->closedAt ??= new DateTimeImmutable;
            $this->closureReason = RoomClosureReason::Completed;
        }
    }

    public function toggleReady(int $userId): void
    {
        $this->ensureStatus(RoomStatus::Waiting);
        $participant = $this->requireParticipant($userId);
        $participant->status = $participant->isReady() ? 'unready' : 'ready';
    }
    // 開始配對流程
    public function startMatching(int $operatorId): void
    {
        $this->ensureHost($operatorId);
        $this->ensureStatus(RoomStatus::Waiting);

        $activeParticipants = $this->activeParticipants();

        if (count($activeParticipants) < 2) {
            throw new RoomRuleException('至少需要兩位參與者才能開始配對。');
        }

        foreach ($activeParticipants as $participant) {
            if (! $participant->isReady()) {
                throw new RoomRuleException('所有參與者都準備好後才能開始配對。');
            }
        }

        $this->status = RoomStatus::Numbering;
        $this->startedAt = new DateTimeImmutable;
    }

    public function assignNumber(int $userId, int $number): void
    {
        $this->ensureStatus(RoomStatus::Numbering);
        $participant = $this->requireParticipant($userId);

        $activeParticipants = $this->activeParticipants();

        if ($number < 1 || $number > count($activeParticipants)) {
            throw new RoomRuleException('請輸入有效的參與者編號。');
        }

        foreach ($activeParticipants as $otherParticipant) {
            if (
                $otherParticipant->userId !== $userId
                && $otherParticipant->participantNumber === $number
            ) {
                throw new RoomRuleException('此編號已被其他參與者使用。');
            }
        }

        $participant->participantNumber = $number;
        // 所有人是否都已輸入編號
        if (! $this->allParticipantsHaveNumber($activeParticipants)) {
            return;
        }

        $this->status = RoomStatus::Voting;
    }

    /**
     * @param  list<int>  $targetUserIds
     */
    public function vote(int $userId, array $targetUserIds): void
    {
        $this->ensureStatus(RoomStatus::Voting);
        $participant = $this->requireParticipant($userId);

        if ($participant->hasVoted()) {
            throw new RoomRuleException('你已經完成投票。');
        }

        $targetUserIds = array_values(array_unique(array_map('intval', $targetUserIds)));
        // 投給的 User id 是否為自己 或 User 是否在房間裡
        foreach ($targetUserIds as $targetUserId) {
            if ($targetUserId === $userId || $this->participant($targetUserId) === null) {
                throw new RoomRuleException('投票對象無效。');
            }
        }

        $participant->votedUserIds = $targetUserIds;
        // 是否全部都已投票完成
        if (! $this->allParticipantsHaveVoted($this->activeParticipants())) {
            return;
        }

        $this->status = RoomStatus::Closed;
        $this->closedAt = new DateTimeImmutable;
        $this->closureReason = RoomClosureReason::Completed;
    }

    /**
     * @return list<array{0: Participant, 1: Participant}>
     */
    public function matches(): array
    {
        if ($this->status !== RoomStatus::Closed) {
            return [];
        }
        // 代入所有房內參與者 key: userId
        $participants = [];
        foreach ($this->participants as $participant) {
            $participants[$participant->userId] = $participant;
        }
        // 所有有配對的人 [ key => [user1, user2], key2 => [...]]
        $matches = [];
        foreach ($this->participants as $participant) {
            foreach ($participant->votedUserIds ?? [] as $targetUserId) {
                $target = $participants[$targetUserId] ?? null;
                // 沒有配對到
                if (
                    $target === null
                    || ! in_array($participant->userId, $target->votedUserIds ?? [], true)
                ) {
                    continue;
                }
                // 配對成功
                $key = min($participant->userId, $targetUserId)
                    .'-'.max($participant->userId, $targetUserId);
                $matches[$key] ??= [$participant, $target];
            }
        }

        return array_values($matches);
    }

    private function participant(int $userId): ?Participant
    {
        foreach ($this->participants as $participant) {
            if ($participant->userId === $userId && ! $participant->hasLeft()) {
                return $participant;
            }
        }

        return null;
    }

    /**
     * @return list<Participant>
     */
    private function activeParticipants(): array
    {
        return array_values(array_filter(
            $this->participants,
            static fn (Participant $participant): bool => ! $participant->hasLeft(),
        ));
    }

    /**
     * @param  list<Participant>  $participants
     */
    private function allParticipantsHaveNumber(array $participants): bool
    {
        foreach ($participants as $participant) {
            if ($participant->participantNumber === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<Participant>  $participants
     */
    private function allParticipantsHaveVoted(array $participants): bool
    {
        foreach ($participants as $participant) {
            if (! $participant->hasVoted()) {
                return false;
            }
        }

        return true;
    }

    private function requireParticipant(int $userId): Participant
    {
        return $this->participant($userId)
            ?? throw new RoomRuleException('你不是此房間的參與者。');
    }

    private function ensureHost(int $operatorId): void
    {
        if ($operatorId !== $this->hostId) {
            throw new RoomRuleException('只有房主可以開始配對。');
        }
    }

    private function ensureStatus(RoomStatus $status): void
    {
        if ($this->status !== $status) {
            throw new RoomRuleException('房間目前不接受此操作。');
        }
    }
}
