<?php

namespace Src\Blog\Comment\Application\Commands;

final readonly class DeleteCommentCommand
{
    public function __construct(
        public int $id,
        public int $operatorId,
    ) {}
}
