<?php

namespace Src\Blog\Comment\Application\CommandHandlers;

use Src\Blog\Comment\Application\Commands\PostCommentCommand;
use Src\Blog\Comment\Domain\Entities\Comment;;

final readonly class PostCommentHandler
{
    public function __construct(
        private readonly PostCommentAction $action,
    ) {}

    public function handle(PostCommentCommand $command): Comment
    {
        return $this->action->handle($command);
    }
}