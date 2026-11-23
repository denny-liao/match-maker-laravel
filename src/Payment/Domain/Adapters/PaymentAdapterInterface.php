<?php

namespace Src\Payment\Domain\Adapters;

use Src\Payment\Domain\Entities\Payment;

interface PaymentAdapterInterface
{
    /** 發起付款，回傳統一標準化的結果 (付款網址或取號資訊) */
    public function charge(Payment $payment, string $desc, string $itemName): array;

    /** 驗證並解析外部 Webhook 傳來的原始資料，翻譯成系統看得懂的 DTO */
    public function parseWebhook(array $rawPayload): WebhookParsedResult;
}
