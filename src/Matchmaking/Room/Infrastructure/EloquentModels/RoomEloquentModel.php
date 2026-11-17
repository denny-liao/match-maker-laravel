<?php

namespace Src\Matchmaking\Room\Infrastructure\EloquentModels;

// use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


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
])]
// #[UseFactory(RoomFactory::class)]
class RoomEloquentModel extends Model
{
    use HasFactory;

    /**
     * 明確指定對應的資料表名稱
     */
    protected $table = 'rooms';
    // Laravel 內建自動雜湊加密
    // JSON 自動轉 PHP 陣列
    protected function casts(): array
    {
        return [
            'max_players' => 'integer',
            'is_private'  => 'boolean',
            'password'    => 'hashed',
            'started_at'  => 'datetime',
            'closed_at'   => 'datetime',
        ];
    }
}
