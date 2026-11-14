<?php

namespace Src\Blog\Comment\Application\Exceptions;

use RuntimeException;

class CommentNotFoundException extends RuntimeException
{
    public function __construct(int $commentId)
    {
        parent::__construct("Comment with ID {$commentId} not found.");
    }
}