<?php

namespace Src\Blog\Comment\Infrastructure\EloquentModels;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


#[Fillable(['message'])]
#[UseFactory(CommentFactory::class)]
class CommentEloquentModel extends Model
{
    use HasFactory;

    /**
     * 明確指定對應的資料表名稱
     */
    protected $table = 'comments';
}