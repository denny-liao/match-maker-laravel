<?php

namespace Src\Blog\Comment\Application\CommandHandlers;

use Src\Blog\Comment\Application\Commands\DeleteCommentCommand;
use Src\Blog\Comment\Application\Commands\EditCommentCommand;
use Src\Blog\Comment\Application\Exceptions\CommentNotFoundException;
use Src\Blog\Comment\Domain\Repositories\CommentRepositoryInterface;
use Src\Blog\Comment\Domain\ValueObjects\AuthorId;

final readonly class DeleteCommentHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
    ) {}

    public function handle(DeleteCommentCommand $command): void
    {
        // 載入聚合根
        $comment = $this->commentRepository->findById($command->id)
            ?? throw new CommentNotFoundException($command->id);

        // 領域規則檢查：例如只有作者或管理員能刪除
        $comment->ensureCanBeDeletedBy($command->operatorId);

        // 持久化變更
        $this->commentRepository->delete($comment);
    }
}