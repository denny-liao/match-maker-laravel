<?php

namespace Src\Payment\Application\Queries;

use Src\Payment\Application\DTOs\PaymentFormDto;
use Src\Payment\Domain\Adapters\PaymentAdapterInterface;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;

final readonly class FindPaymentByTradeNoQuery
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private PaymentAdapterInterface $paymentAdapter
    ) {}
    public function handle(string $tradeNo, string $itemName, string $tradeDesc): array
    {
        $payment = $this->paymentRepo->findByTradeNo($tradeNo);
        $formData = $this->paymentAdapter->charge($payment, $tradeDesc, $itemName);

        return [
            'info' => new PaymentFormDto(
            MerchantID: $formData['MerchantID'],
            MerchantTradeNo: $formData['MerchantTradeNo'],
            MerchantTradeDate: $formData['MerchantTradeDate'],
            PaymentType: $formData['PaymentType'],
            TotalAmount: $formData['TotalAmount'],
            TradeDesc: $formData['TradeDesc'],
            ItemName: $formData['ItemName'],
            ReturnURL: $formData['ReturnURL'],
            PaymentInfoURL: $formData['PaymentInfoURL'],
            ChoosePayment: $formData['ChoosePayment'],
            CheckMacValue: $formData['CheckMacValue'],
            EncryptType: $formData['EncryptType'],
        ),
        'status' => $payment->status->value
        ];
    }
}
