<?php

namespace Src\Blog\Comment\Domain\Repositories;

use Src\Blog\Comment\Domain\Entities\Comment;

interface CommentRepositoryInterface 
{
    public function findById(string $id): ?Comment;
    public function save(Comment $comment): ?Comment;
    public function delete(Comment $comment): void;
}