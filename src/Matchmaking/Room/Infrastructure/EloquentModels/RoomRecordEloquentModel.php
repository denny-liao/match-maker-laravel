<?php

namespace Src\Matchmaking\Room\Infrastructure\EloquentModels;

// use Database\Factories\RoomRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


#[Fillable([
    'room_id',
    'user_id',
    'participant_number',
    'role',
    'status',
    'joined_at',
    'left_at',
    'voted_user_ids',
])]
// #[UseFactory(RoomRecordFactory::class)]
class RoomRecordEloquentModel extends Model
{
    use HasFactory;

    /**
     * 明確指定對應的資料表名稱
     */
    protected $table = 'room_records';

    protected function casts(): array
    {
        return [
            'joined_at'      => 'datetime',
            'left_at'        => 'datetime',
            'voted_user_ids' => 'array', // 資料庫 JSON 自動轉整數陣列
        ];
    }
}
