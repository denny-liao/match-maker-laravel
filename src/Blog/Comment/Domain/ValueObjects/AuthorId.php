<?php
namespace Src\Blog\Comment\Domain\ValueObjects;

final readonly class AuthorId
{
    public function __construct(public int $value) {}
}