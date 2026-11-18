<?php

namespace Src\Matchmaking\Room\Domain\Enums;

enum RoomClosureReason: string
{
    case Completed = 'completed';
    case ParticipantsLeft = 'participants_left';
}
