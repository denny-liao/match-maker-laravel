<?php

namespace Src\Payment\Application\Commands;

readonly class CreatePaymentCommand
{
    public function __construct(
        public int $referenceId,
        public int $amount,
        public string $channel,   // ecpay
        public string $tradeDesc,
        public string $itemName,
        public ?int $userId = null,
    ) {}
}