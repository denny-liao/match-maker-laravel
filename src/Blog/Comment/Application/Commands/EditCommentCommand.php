<?php

namespace Src\Blog\Comment\Application\Commands;

final readonly class EditCommentCommand
{
    public function __construct(
        public int $id,
        public ?int $operatorId = null,
        public ?string $message = null,
        public ?int $amount = null,
    ) {}
}
