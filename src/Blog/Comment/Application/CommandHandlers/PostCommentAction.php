<?php

namespace Src\Blog\Comment\Application\CommandHandlers;

use Src\Blog\Comment\Application\Commands\PostCommentCommand;
use Src\Blog\Comment\Domain\Entities\Comment;
use Src\Blog\Comment\Domain\Repositories\CommentRepositoryInterface;

final readonly class PostCommentAction
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
    ) {}

    public function handle(PostCommentCommand $command): Comment
    {
       // 1. 建立尚未有 ID 的新聚合根
        $comment = Comment::create(
            authorId: $command->operatorId,
            message: $command->message,
            authorName: $command->authorName,
            amount: $command->amount,
        );

        // 2. 交給 Repository 儲存（儲存後 $comment->id 就會自動被填上）
        $comment = $this->commentRepository->save($comment);


        // 3. 回傳完整的聚合根（此時已包含資料庫生成的真實 ID）
        return $comment;
    }
}