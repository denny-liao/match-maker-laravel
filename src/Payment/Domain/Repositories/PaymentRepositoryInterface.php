<?php

namespace Src\Payment\Domain\Repositories;

use Closure;
use Src\Payment\Domain\Entities\Payment;

interface PaymentRepositoryInterface
{
    public function create(Payment $payment): Payment;

    public function findByTradeNo(string $tradeNo): ?Payment;

    public function mutate(string $tradeNo, Closure $operation): Payment;
}
