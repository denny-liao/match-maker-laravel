<?php

namespace Src\Blog\Comment\Domain\Entities;

use Src\Blog\Comment\Domain\ValueObjects\AuthorId;
use Src\Common\Domain\AggregateRoot;

final class Comment extends AggregateRoot
{

    public function __construct(
        public ?int $id,
        public readonly AuthorId $authorId,
        public string $message,
    ) {}

    // 專門用來「建立全新留言」的命名建構子（此時還沒有 ID）
    public static function create(int $authorId, string $message): self
    {
        $comment = new self(
            id: null,
            authorId: new AuthorId($authorId),
            message: $message,
        );

        return $comment;
    }

    public function editMessage(string $message, int $operatorId): void
    {
        // 驗證操作員是否為作者
        if ($this->authorId->value !== $operatorId) {
            throw new \DomainException('Only the author can edit this comment.');
        }

        // 更新留言內容
        $this->message = $message;
    }

    public function ensureCanBeDeletedBy(int $operatorId) {
        if ($this->authorId->value !== $operatorId) {
            throw new \DomainException('Only the author can delete this comment.');
        }
    }
}
