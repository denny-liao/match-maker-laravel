<?php

namespace Src\Matchmaking\Room\Application\DTOs;

use Src\Matchmaking\Room\Domain\Entities\Participant;
use Src\Matchmaking\Room\Domain\Entities\Room;

final class RoomData
{
    /**
     * @return array{
     *   id: int|null,
     *   code: string,
     *   title: string,
     *   hostId: int,
     *   status: string,
     *   closureReason: string|null,
     *   maxPlayers: int,
     *   participantCount: int,
     *   readyCount: int,
     *   numberedCount: int,
     *   votedCount: int,
     *   participants: list<array{
     *     userId: int,
     *     name: string,
     *     role: string,
     *     status: string,
     *     participantNumber: int|null,
     *     hasVoted: bool
     *   }>,
     *   matches: list<array{
     *     first: array{number: int|null, name: string},
     *     second: array{number: int|null, name: string}
     *   }>
     * }
     */
    public static function fromRoom(Room $room): array
    {
        $participants = array_map(
            static fn (Participant $participant): array => [
                'userId' => $participant->userId,
                'name' => $participant->name,
                'role' => $participant->role,
                'status' => $participant->status,
                'participantNumber' => $participant->participantNumber,
                'hasVoted' => $participant->hasVoted(),
            ],
            array_values(array_filter(
                $room->participants,
                static fn (Participant $participant): bool => ! $participant->hasLeft(),
            )),
        );

        return [
            'id' => $room->id,
            'code' => $room->code,
            'title' => $room->title,
            'hostId' => $room->hostId,
            'status' => $room->status->value,
            'closureReason' => $room->closureReason?->value,
            'maxPlayers' => $room->maxPlayers,
            'participantCount' => count($participants),
            'readyCount' => count(array_filter(
                $participants,
                static fn (array $participant): bool => $participant['status'] === 'ready',
            )),
            'numberedCount' => count(array_filter(
                $participants,
                static fn (array $participant): bool => $participant['participantNumber'] !== null,
            )),
            'votedCount' => count(array_filter(
                $participants,
                static fn (array $participant): bool => $participant['hasVoted'],
            )),
            'participants' => $participants,
            'matches' => array_map(
                static fn (array $match): array => [
                    'first' => [
                        'number' => $match[0]->participantNumber,
                        'name' => $match[0]->name,
                    ],
                    'second' => [
                        'number' => $match[1]->participantNumber,
                        'name' => $match[1]->name,
                    ],
                ],
                $room->matches(),
            ),
        ];
    }
}
