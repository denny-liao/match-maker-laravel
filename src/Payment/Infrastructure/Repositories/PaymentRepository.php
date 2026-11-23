<?php

namespace Src\Payment\Infrastructure\Repositories;

use Closure;
use Illuminate\Support\Facades\DB;
use Src\Payment\Domain\Entities\Payment;
use Override;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Src\Payment\Infrastructure\EloquentModels\PaymentEloquentModel;
use Src\Payment\Infrastructure\Mappers\PaymentMapper;

final class PaymentRepository implements PaymentRepositoryInterface
{

    #[Override]
    public function create(Payment $payment): Payment
    {
        $model = PaymentEloquentModel::query()->create([
            'reference_id' => $payment->referenceId,
            'trade_no' => $payment->tradeNo,
            'trade_date' => $payment->tradeDate,
            'channel' => $payment->channel,
            'amount' => $payment->amount,
            'status' => $payment->status,
        ]);

        return PaymentMapper::toDomain($model);
    }

    #[Override]
    public function findByTradeNo(string $tradeNo): ?Payment
    {
        $model = PaymentEloquentModel::query()->where('trade_no', '=', $tradeNo)->firstOrFail();

        return PaymentMapper::toDomain($model);
    }

    #[Override]
    public function mutate(string $tradeNo, Closure $operation): Payment
    {
        return DB::transaction(function () use ($tradeNo, $operation): Payment {

            $model = PaymentEloquentModel::query()
                ->where('trade_no', '=', $tradeNo)
                ->lockForUpdate()
                ->firstOrFail();

            $payment = PaymentMapper::toDomain($model);

            $operation($payment);

            PaymentMapper::toPersistence($model, $payment)->save();

            return $payment;
        });
    }
}
