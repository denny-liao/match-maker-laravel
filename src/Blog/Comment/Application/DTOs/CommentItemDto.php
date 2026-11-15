<?php

namespace Src\Blog\Comment\Application\DTOs;

final readonly class CommentItemDto
{
    public function __construct(
        public int $id,
        public string $message,
        public string $authorName,
        public string $createdAt,
        public string $avatar = ''
    ) {}
}