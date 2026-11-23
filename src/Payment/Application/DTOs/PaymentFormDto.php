<?php

namespace Src\Payment\Application\DTOs;

readonly class PaymentFormDto
{
    public function __construct(
        public string $MerchantID,
        public string $MerchantTradeNo,
        public string $MerchantTradeDate,
        public string $PaymentType,
        public string $TotalAmount,
        public string $TradeDesc,
        public string $ItemName,
        public string $ReturnURL,
        public string $ChoosePayment,
        public string $CheckMacValue,
        public string $EncryptType
    ) {}
}
