<?php

namespace Src\Payment\Infrastructure\Adapters;

use DateTimeImmutable;
use RuntimeException;
use Src\Common\Domain\Clock\Clock;
use Src\Payment\Domain\Adapters\PaymentAdapterInterface;
use Src\Payment\Domain\Adapters\WebhookParsedResult;
use Src\Payment\Domain\Entities\Payment;

class EcpayAdapter implements PaymentAdapterInterface
{
    private string $hashKey;
    private string  $hashIV;

    public function __construct(
        private Clock $clock
    ) {
        $this->hashKey = config('custom.payment.ecpay.hash_key');
        $this->hashIV = config('custom.payment.ecpay.hash_iv');
    }

    /** 發起付款，回傳統一標準化的結果 (付款網址或取號資訊) */
    public function charge(Payment $payment, string $tradeDesc, string $itemName): array
    {
        $formData = [
            'MerchantID' => config('custom.payment.ecpay.merchant_id'),
            'MerchantTradeNo' => $payment->tradeNo,
            'MerchantTradeDate' => $payment->tradeDate,
            'PaymentType' => 'aio',
            'TotalAmount' => $payment->amount,
            'TradeDesc' => $tradeDesc,
            'ItemName' => $itemName,
            'ReturnURL' => '', // TODO: ngrok
            'ChoosePayment' => 'ALL',
            'CheckMacValue' => '',
            'EncryptType' => 1
        ];

        $macValue = $this->generateEcpayCheckMacValue($formData);
        $formData['CheckMacValue'] = $macValue;

        return $formData;
    }

// payment info
// ATM 虛擬帳號範例
// {
//   "type": "ATM",
//   "bank_code": "822",
//   "virtual_account": "91234567890123"
// }

// 超商代碼範例
// {
//   "type": "CVS",
//   "payment_no": "LL261121123456",
//   "store_type": "IBON"
// }

// raw response
// {
//   "code_issued": {
//     "received_at": "2026-11-21T16:30:00+08:00",
//     "payload": { "RtnCode": "2", "BankCode": "822", "vAccount": "91234567890123" }
//   },
//   "payment_result": {
//     "received_at": "2026-11-21T18:15:22+08:00",
//     "payload": { "RtnCode": "1", "TradeNo": "2611211630001234", "TradeAmt": "1000" }
//   }
// }
    /** 驗證並解析外部 Webhook 傳來的原始資料，翻譯成系統看得懂的 DTO */
    public function parseWebhook(array $rawPayload): WebhookParsedResult
    {

        // if (! empty($rawPayload['SimulatePaid'])) {
        //     throw new RuntimeException('交易模擬付款。');
        // }

        // 驗證綠界 CheckMacValue
        if (! $this->verifyCheckMacValue($rawPayload)) {
            throw new RuntimeException('綠界簽章驗證失敗。');
        }

        // 交易狀態
        $eventStatus = match ($rawPayload['RtnCode']) {
            1 => 'payment_succeeded',
            2, 10100073 => 'code_issued',
            default => 'payment_failed'
        };

        // 取號資訊
        $paymentInfo = match ($rawPayload['RtnCode'] ?? '') {
            2 => [
                'type' => 'ATM',
                'bank_code' => $rawPayload['BankCode'],
                'virtual_account' => $rawPayload['vAccount']
            ],
            10100073 => [
                'type' => !empty($rawPayload['PaymentNo']) ? 'CVS' : 'BARCODE',
                'payment_no' => $rawPayload['PaymentNo'],
            ],
            default => null,
        };


        $rawResponse[$eventStatus] = [
            "received_at" => $this->clock->now(),
            "payload" => $rawPayload
        ];


        return new WebhookParsedResult(
            tradeNo: $rawPayload['MerchantTradeNo'],
            channel: 'ecpay',
            channelTradeNo: $rawPayload['TradeNo'],
            amount: $rawPayload['TradeAmt'],
            eventStatus: $eventStatus,
            expireAt: !empty($rawPayload['ExpireDate']) ? new DateTimeImmutable($rawPayload['ExpireDate']) : null,
            paymentInfo: $paymentInfo,
            rawResponse: $rawResponse,
            paidAt: !empty($rawPayload['PaymentDate']) ? new DateTimeImmutable($rawPayload['PaymentDate']) : null,
        );
    }

    private function verifyCheckMacValue(array $payload): bool
    {
        $payloadCheckMacValue = $payload['CheckMacValue'];
        $checkMacValue = $this->generateEcpayCheckMacValue($payload);
        return $payloadCheckMacValue === $checkMacValue;
    }

    private function generateEcpayCheckMacValue(array $params): string
    {
        // 1. 移除 CheckMacValue 欄位
        unset($params['CheckMacValue']);

        // 2. 依參數名稱做不區分大小寫的 ASCII 排序（A -> Z）
        uksort($params, function ($a, $b) {
            return strcasecmp($a, $b);
        });

        // 3. 組合字串並前後加上 HashKey 與 HashIV
        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = "{$key}={$value}";
        }
        $raw = "HashKey={$this->hashKey}&" . implode('&', $pairs) . "&HashIV={$this->hashIV}";

        // 4. URL encode
        $encoded = urlencode($raw);

        // 5. 取代為符合 .NET UrlEncode 規則的字元
        $dotNetMap = [
            '%2d' => '-',
            '%5f' => '_',
            '%2e' => '.',
            '%21' => '!',
            '%2a' => '*',
            '%28' => '(',
            '%29' => ')',
            '%20' => '+'
        ];
        foreach ($dotNetMap as $key => $value) {
            str_replace($key, $value, $encoded);
        }

        // 6. 轉為全小寫
        $lower = strtolower($encoded);

        // 7. SHA256 雜湊並轉為全大寫
        return strtoupper(hash('sha256', $lower));
    }
}
