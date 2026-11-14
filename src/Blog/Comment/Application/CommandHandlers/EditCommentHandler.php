<?php

namespace Src\Blog\Comment\Application\CommandHandlers;

use Src\Blog\Comment\Application\Commands\EditCommentCommand;
use Src\Blog\Comment\Application\Exceptions\CommentNotFoundException;
use Src\Blog\Comment\Domain\Repositories\CommentRepositoryInterface;
use Src\Blog\Comment\Domain\ValueObjects\AuthorId;

final readonly class EditCommentHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
    ) {}

    public function handle(EditCommentCommand $command): void
    {
        // 載入聚合根
        $comment = $this->commentRepository->findById($command->id)
            ?? throw new CommentNotFoundException($command->id);

        // 執行領域行為（保護業務規則）
        $comment->editMessage(
            $command->message,
            new AuthorId($command->operatorId)
        );

        // 持久化變更
        $this->commentRepository->save($comment);
    }
}