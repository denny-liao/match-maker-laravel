<?php

namespace Src\Payment\Application\Commands;

readonly class ProcessEcpayWebhookCommand {
    
    public function __construct(
        public string $tradeNo,
    )
    {}
}