<?php

namespace Src\Blog\Comment\Application\DTOs;

final readonly class CommentItemDto
{
    public function __construct(
        public int $id,
        public string $message,
        public string $createdAt,
        public ?int $authorId,
        public string $authorName = '',
        public string $avatar = ''
    ) {}
}