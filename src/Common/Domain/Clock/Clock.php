<?php

namespace Src\Common\Domain\Clock;

use DateTimeImmutable;
use DateTimeZone;

interface Clock
{
    public function now(): DateTimeImmutable;
    
    public function timezone(): DateTimeZone;

    public function fromString(string $datetime): DateTimeImmutable;

    public function setTimezone(DateTimeImmutable $date): DateTimeImmutable;
}
