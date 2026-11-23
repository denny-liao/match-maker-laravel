<?php

namespace Src\Payment\Domain\Entities;

use DateTimeImmutable;
use Src\Common\Domain\AggregateRoot;
use Src\Payment\Domain\Adapters\WebhookParsedResult;
use Src\Payment\Domain\Enums\PaymentStatus;
use Src\Payment\Domain\Exceptions\PaymentRuleException;

final class Payment extends AggregateRoot
{
    public function __construct(
        public ?int $id,
        public readonly int $referenceId,
        public readonly string $tradeNo,
        public DateTimeImmutable $tradeDate,
        public readonly string $channel,
        public readonly int $amount,
        public PaymentStatus $status,
        public ?string $channelTradeNo = null,
        public ?string $note = null,
        public ?DateTimeImmutable $paidAt = null,
        public ?DateTimeImmutable $expireAt = null,
        public ?array $paymentInfo = null, // 非即時付款用，ex. ATM、CVS、barcode
        public ?array $rawResponse = null,
    ) {}

    public static function create(
        int $referenceId,
        string $tradeNo,
        DateTimeImmutable $tradeDate,
        int $amount,
        string $channel = 'ecpay',
    ) {
        if (strlen($tradeNo) > 20) {
            throw new PaymentRuleException('交易編號長度必須小於 20。');
        }

        return new self(
            id: null,
            referenceId: $referenceId,
            tradeNo: $tradeNo,
            tradeDate: $tradeDate,
            channel: $channel,
            amount: $amount,
            status: PaymentStatus::Pending
        );
    }

    // 取號結果通知
    public function receivedInfo(WebhookParsedResult $result)
    {
        $this->rawResponse[] = $result->rawResponse;

        if ($result->isPaymentFailed()) {
            $this->status = PaymentStatus::Failed;
            throw new PaymentRuleException('付款失敗。');
        } else if ($result->isPaymentInfo()) {
            throw new PaymentRuleException('非取號結果通知。');
        }

        if ($this->status !== PaymentStatus::Pending) {
            throw new PaymentRuleException('目前交易狀態需為處理中。');
        }

        $this->ensureData($result);
        $this->channelTradeNo = $result->channelTradeNo;
        $this->expireAt = $result->expireAt;
        $this->paymentInfo = $result->paymentInfo;
        $this->status = PaymentStatus::WaitingPayment;
    }

    // 付款結果通知
    public function receivedResult(WebhookParsedResult $result)
    {
        $this->rawResponse[] = $result->rawResponse;

        if ($result->isPaymentFailed()) {
            $this->status = PaymentStatus::Failed;
            throw new PaymentRuleException('付款失敗。');
        } else if (! $result->isPaymentSucceeded()) {
            throw new PaymentRuleException('付款結果未成功。');
        }

        if ($this->status !== PaymentStatus::Pending && $this->status !== PaymentStatus::WaitingPayment) {
            throw new PaymentRuleException('目前交易狀態需為處理中或已取號待付款。');
        }

        if ($this->status === PaymentStatus::WaitingPayment && $this->channelTradeNo !== $result->channelTradeNo) {
            throw new PaymentRuleException('已取號金流渠道交易編號與付款結果通知不符');
        }

        $this->ensureData($result);
        $this->channelTradeNo = $result->channelTradeNo;
        $this->paidAt = $result->paidAt;
        $this->paymentInfo = $result->paymentInfo;
        $this->status = PaymentStatus::Success;
    }

    public function ensureData(WebhookParsedResult $result)
    {
        if ($this->tradeNo !== $result->tradeNo) {
            throw new PaymentRuleException('訂單交易編號不符。');
        }

        if ($this->channel !== $result->channel) {
            throw new PaymentRuleException('交易渠道錯誤。');
        }

        if ($this->amount !== $result->amount) {
            throw new PaymentRuleException('交易總金額錯誤。');
        }
    }
}
