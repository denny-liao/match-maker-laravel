<?php

namespace Src\Blog\Comment\Application\Commands;

final readonly class EditCommentCommand
{
    public function __construct(
        public int $id,
        public int $operatorId,
        public string $message,
    ) {}
}