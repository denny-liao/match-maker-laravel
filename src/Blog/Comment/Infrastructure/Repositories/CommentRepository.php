<?php

namespace Src\Blog\Comment\Infrastructure\Repositories;

use Src\Blog\Comment\Domain\Entities\Comment;
use Src\Blog\Comment\Domain\Repositories\CommentRepositoryInterface;
use Src\Blog\Comment\Infrastructure\EloquentModels\CommentEloquentModel;

class CommentRepository implements CommentRepositoryInterface
{
    public function findById(string $id): ?Comment
    {
        // 使用 Eloquent 模型查找 Comment
        $commentEloquentModel = CommentEloquentModel::find($id);

        if (!$commentEloquentModel) {
            return null;
        }

        // 將 Eloquent 模型轉換為領域實體
        return new Comment(
            id: $commentEloquentModel->id,
            message: $commentEloquentModel->message,
            authorId: $commentEloquentModel->author_id,
            authorName: $commentEloquentModel->author_name,
            amount: $commentEloquentModel->donate_amount,
            status: $commentEloquentModel->status
        );
    }
    public function save(Comment $comment): ?Comment
    {
        if ($comment->id === null) {
            // 情況一：全新留言，讓資料庫自動產生 ID
            $model = CommentEloquentModel::create([
                'author_id' => $comment->authorId,
                'message' => $comment->message,
                'author_name' => $comment->authorName,
                'donate_amount' => $comment->amount,
                'status' => $comment->status
            ]);

            return $comment->assignId($model->id);
        }

        // 情況二：已存在的留言，執行更新
        CommentEloquentModel::where('id', $comment->id)->update([
            'message' => $comment->message,
        ]);

        return null;
    }
    public function delete(Comment $comment): void
    {
        // 使用 Eloquent 模型刪除 Comment
        CommentEloquentModel::destroy($comment->id);
    }
}
