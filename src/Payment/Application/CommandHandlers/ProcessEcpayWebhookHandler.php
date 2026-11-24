<?php

namespace Src\Payment\Application\CommandHandlers;

use Src\Payment\Application\Commands\ProcessEcpayWebhookCommand;
use Src\Payment\Domain\Adapters\PaymentAdapterInterface;
use Src\Payment\Domain\Entities\Payment;
use Src\Payment\Domain\Enums\PaymentStatus;
use Src\Payment\Domain\Events\PaymentCompleted;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;

final readonly class ProcessEcpayWebhookHandler {

    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private PaymentAdapterInterface $paymentAdapter,
    )
    {}

    public function handle(ProcessEcpayWebhookCommand $command, array $rawPayload): Payment {

        $result = $this->paymentAdapter->parseWebhook($rawPayload);

        $payment = $this->paymentRepo->mutate($command->tradeNo, function (Payment $payment) use($result): void {
            $payment->headToUpdate($result);
        });

        PaymentCompleted::dispatchIf(
            $payment->status === PaymentStatus::Success,
            referenceId: $payment->referenceId,
            amount: $payment->amount
        );

        return $payment;
    }
}