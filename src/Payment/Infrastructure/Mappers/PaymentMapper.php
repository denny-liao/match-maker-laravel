<?php

namespace Src\Payment\Infrastructure\Mappers;

use Src\Payment\Domain\Entities\Payment;
use Src\Payment\Domain\Enums\PaymentStatus;
use Src\Payment\Infrastructure\EloquentModels\PaymentEloquentModel;

final class PaymentMapper
{
    public static function toDomain(PaymentEloquentModel $model): Payment
    {
        return new Payment(
            id: $model->id,
            referenceId: $model->reference_id,
            tradeNo: $model->trade_no,
            tradeDate: $model->trade_date,
            channel: $model->channel,
            channelTradeNo: $model->channel_trade_no,
            amount: $model->amount,
            status: PaymentStatus::from($model->status),
            note: $model->note,
            paidAt: $model->paid_at,
            expireAt: $model->expire_at,
            paymentInfo: $model->payment_info,
            rawResponse: $model->raw_response,
        );
    }

    public static function toPersistence(PaymentEloquentModel $model, Payment $payment): PaymentEloquentModel 
    {
       return $model->fill([
            'channel_trade_no' =>  $payment->channelTradeNo,
            'status' => $payment->status->value,
            'note' =>  $payment->note,
            'paid_at' =>  $payment->paidAt,
            'expire_at' =>  $payment->expireAt,
            'payment_info' =>  $payment->paymentInfo,
            'raw_response' =>  $payment->rawResponse,
        ]);
    }
}
