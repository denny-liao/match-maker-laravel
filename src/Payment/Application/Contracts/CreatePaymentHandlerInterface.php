<?php

namespace Src\Payment\Application\Contracts;

use Src\Payment\Application\Commands\CreatePaymentCommand;
use Src\Payment\Application\DTOs\PaymentFormDto;

interface CreatePaymentHandlerInterface {
    public function handle(CreatePaymentCommand $command): PaymentFormDto;
}
