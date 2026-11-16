<?php

namespace Src\Blog\Comment\Application\DTOs;

final readonly class CommentEditDto
{
    public function __construct(
        public int $id,
        public string $message,
    ) {}
}