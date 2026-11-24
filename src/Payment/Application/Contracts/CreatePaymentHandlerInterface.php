<?php

namespace Src\Payment\Application\Contracts;

use Src\Payment\Application\Commands\CreatePaymentCommand;

interface CreatePaymentHandlerInterface {
    public function handle(CreatePaymentCommand $command): string;
}
