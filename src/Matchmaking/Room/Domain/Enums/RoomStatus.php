<?php

namespace Src\Matchmaking\Room\Domain\Enums;

enum RoomStatus: string
{
    case Waiting = 'waiting';
    case Numbering = 'numbering';
    case Voting = 'voting';
    case Closed = 'closed';
}
