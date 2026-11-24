<?php

namespace Src\Payment\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $referenceId,
        public float $amount
    ) {}
}
