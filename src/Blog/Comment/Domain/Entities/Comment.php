<?php

namespace Src\Blog\Comment\Domain\Entities;

use Src\Blog\Comment\Domain\Enums\CommentStatus;
use Src\Common\Domain\AggregateRoot;

final class Comment extends AggregateRoot
{

    public function __construct(
        public private(set) ?int $id,
        public readonly int $authorId,
        public string $message,
        public readonly ?string $authorName,
        public readonly ?int $amount,
        public private(set) CommentStatus $status
    ) {}

    // 專門用來「建立全新留言」的命名建構子（此時還沒有 ID）
    public static function create(int $authorId, string $authorName, string $message, int $amount): self
    {
        if ($amount < 0) {
            throw new \DomainException('贊助金額不能為負數');
        }
        if ($amount > 0 && $amount < 10) {
            throw new \DomainException('單筆贊助最低金額為 10 元');
        }

        $comment = new self(
            id: null,
            authorId: $authorId,
            authorName: $authorName,
            message: $message,
            amount: $amount,
            status: $amount > 0 ? CommentStatus::Pending : CommentStatus::Published
        );

        return $comment;
    }

    public function assignId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    public function editComment(?string $message, ?int $operatorId, int $amount): void
    {
        // 驗證操作員是否為作者
        if (isset($operatorId) && $this->authorId !== $operatorId) {
            throw new \DomainException('Only the author can edit this comment.');
        }

        if ($this->amount !== $amount) {
            throw new \DomainException('留言金額不符。');
        }

        if ($this->status === CommentStatus::Published) {
            // 更新留言內容
            $this->message = $message;
            return;
        }

        $this->publish();
    }

    public function ensureCanBeDeletedBy(int $operatorId)
    {
        if ($this->authorId !== $operatorId) {
            throw new \DomainException('Only the author can delete this comment.');
        }
    }

    // 金流付款成功後發布留言
    public function publish(): void
    {
        if($this->status !== CommentStatus::Pending) {
            return;
        }

        $this->status = CommentStatus::Published;
    }
}
