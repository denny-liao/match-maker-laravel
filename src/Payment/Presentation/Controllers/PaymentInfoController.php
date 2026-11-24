<?php

namespace Src\Payment\Presentation\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Payment\Application\Queries\FindPaymentByTradeNoQuery;

class PaymentInfoController
{
    public function __construct(
        private FindPaymentByTradeNoQuery $query
    ) {}

    public function __invoke(Request $request): Response
    {
        $data = $this->query->handle($request->trade_no, '站內留言', '數位工具開發與技術服務支持');

        return Inertia::render('Payments/ConfirmInfo', [
            'info' => $data['info'],
            'status' => $data['status']
        ]);
    }
}
