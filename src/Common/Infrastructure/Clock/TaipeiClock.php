<?php

namespace Src\Common\Infrastructure\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Src\Common\Domain\Clock\Clock;


final class TaipeiClock implements Clock
{
    public const TIMEZONE_NAME = 'Asia/Taipei';
    private DateTimeZone $timezone;

    public function __construct()
    {
        $this->timezone = new DateTimeZone(self::TIMEZONE_NAME);
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }

    public function timezone(): DateTimeZone
    {
        return $this->timezone;
    }

    public function fromString(string $datetime): DateTimeImmutable
    {
        return new DateTimeImmutable($datetime, $this->timezone);
    }
}
