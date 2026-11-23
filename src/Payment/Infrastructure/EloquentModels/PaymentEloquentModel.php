<?php

namespace Src\Payment\Infrastructure\EloquentModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'reference_id',
    'trade_no',
    'trade_date',
    'channel',
    'channel_trade_no',
    'amount',
    'status',
    'note',
    'paid_at',
    'expire_at',
    'payment_info',
    'raw_response',
])]
/**
 * @property int $id
 * @property int $reference_id
 * @property string $trade_no
 * @property Carbon $trade_date
 * @property string $channel
 * @property string|null $channel_trade_no
 * @property int $amount
 * @property string $status
 * @property Carbon|null $paid_at
 * @property Carbon|null $expire_at
 * @property string|null $note
 * @property \Illuminate\Support\Collection $payment_info
 * @property \Illuminate\Support\Collection $raw_response
 */
class PaymentEloquentModel extends Model
{
     /**
     * 明確指定對應的資料表名稱
     */
    protected $table = 'payments';

    protected function casts(): array
    {
        return [
            'trade_date' => 'immutable_datetime',
            'paid_at' => 'immutable_datetime',
            'expire_at' => 'immutable_datetime',
            'payment_info' => 'array',
            'raw_response' => 'array',
        ];
    }
}