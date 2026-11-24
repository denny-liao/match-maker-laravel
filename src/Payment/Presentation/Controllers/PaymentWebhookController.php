<?php

namespace Src\Payment\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Src\Payment\Application\CommandHandlers\ProcessEcpayWebhookHandler;
use Src\Payment\Application\Commands\ProcessEcpayWebhookCommand;

class PaymentWebhookController
{
    public function __construct(
        private ProcessEcpayWebhookHandler $ecpayHandler
    ) {}

    public function handleEcpay(Request $request): Response
    {
        $payload = $request->all();
        $this->ecpayHandler->handle(new ProcessEcpayWebhookCommand(
            tradeNo: $payload['MerchantTradeNo']
        ), $payload);

        // 綠界規定收到通知後必須固定回傳 '1|OK'，否則會持續重發
        return response('1|OK', 200)->header('Content-Type', 'text/plain');
    }
}
