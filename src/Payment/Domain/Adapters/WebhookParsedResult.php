<?php

namespace Src\Payment\Domain\Adapters;

use DateTimeImmutable;

final readonly class WebhookParsedResult
{
    public function __construct(
        public string $tradeNo,
        public string $channel,
        public string $channelTradeNo,
        public int $amount,
        public string $eventStatus,
        public DateTimeImmutable $expireAt,
        public array $paymentInfo,
        public array $rawResponse,
        public ?DateTimeImmutable $paidAt = null,
    ) {}

    public function isPaymentInfo()
    {
        return $this->eventStatus === 'code_issued';
    }

    public function isPaymentSucceeded()
    {
        return $this->eventStatus === 'payment_succeeded';
    }

    public function isPaymentFailed()
    {
        return $this->eventStatus === 'payment_failed';
    }
}
