<?php

namespace Src\Matchmaking\Room\Infrastructure\EloquentModels;

// use Database\Factories\RoomRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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
/**
 * @property int $id
 * @property int $room_id
 * @property int $user_id
 * @property string $user_name
 * @property string $role
 * @property string $status
 * @property int|null $participant_number
 * @property Carbon|null $joined_at
 * @property Carbon|null $left_at
 * @property list<int>|null $voted_user_ids
 */
// #[UseFactory(RoomRecordFactory::class)]
class RoomRecordEloquentModel extends Model
{
    /**
     * 明確指定對應的資料表名稱
     */
    protected $table = 'room_records';

    /**
     * @return BelongsTo<RoomEloquentModel, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(RoomEloquentModel::class, 'room_id');
    }

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'voted_user_ids' => 'array', // 資料庫 JSON 自動轉整數陣列
        ];
    }
}
