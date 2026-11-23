<?php

namespace Src\Blog\Comment\Application\Commands;

final readonly class PostCommentCommand
{
    public function __construct(
        public int $operatorId,
        public string $message,
        public string $authorName,
        public int $amount
    ) {}
}