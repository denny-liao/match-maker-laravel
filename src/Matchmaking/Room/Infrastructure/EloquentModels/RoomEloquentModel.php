<?php

namespace Src\Matchmaking\Room\Infrastructure\EloquentModels;

// use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'code',
    'host_id',
    'title',
    'status',
    'max_players',
    'is_private',
    'password',
    'started_at',
    'closed_at',
    'closure_reason',
])]
/**
 * @property int $id
 * @property string $code
 * @property int $host_id
 * @property string $title
 * @property string $status
 * @property int $max_players
 * @property Carbon|null $started_at
 * @property Carbon|null $closed_at
 * @property string|null $closure_reason
 */
// #[UseFactory(RoomFactory::class)]
class RoomEloquentModel extends Model
{
    /**
     * 明確指定對應的資料表名稱
     */
    protected $table = 'rooms';

    /**
     * @return HasMany<RoomRecordEloquentModel, $this>
     */
    public function records(): HasMany
    {
        return $this->hasMany(RoomRecordEloquentModel::class, 'room_id');
    }

    // Laravel 內建自動雜湊加密
    // JSON 自動轉 PHP 陣列
    protected function casts(): array
    {
        return [
            'max_players' => 'integer',
            'is_private' => 'boolean',
            'password' => 'hashed',
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
