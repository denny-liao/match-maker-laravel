<?php

namespace Src\Payment\Application\CommandHandlers;

use DateTimeImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Src\Common\Domain\Clock\Clock;
use Src\Payment\Application\Commands\CreatePaymentCommand;
use Src\Payment\Application\Contracts\CreatePaymentHandlerInterface;
use Src\Payment\Domain\Entities\Payment;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;

final readonly class CreatePaymentHandler implements CreatePaymentHandlerInterface
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private Clock $clock,
    ) {}
    public function handle(CreatePaymentCommand $command): string
    {
        $maxAttempts = 3;
        $attempt = 0;
        $tradeNumber = null;
        while ($attempt < $maxAttempts) {
            try {

                $date = $this->clock->now();
                $dateStr = $date->format('Ymd');
                $random = strtoupper(bin2hex(random_bytes(4)));
                $tradeNumber = "ORD{$dateStr}{$random}";

                $this->paymentRepo->create(Payment::create(
                    referenceId: $command->referenceId,
                    amount: $command->amount,
                    channel: $command->channel,
                    tradeNo: $tradeNumber,
                    tradeDate: new DateTimeImmutable('now'),
                ));
                break; // 寫入成功，跳出迴圈
            } catch (UniqueConstraintViolationException $e) {
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    // 重試 3 次仍失敗則拋出例外
                    throw new \RuntimeException('無法產生有效的交易編號，請稍後再試。', 500, $e);
                }
            }
        }
        return $tradeNumber;
    }
}
