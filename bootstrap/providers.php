<?php

use Src\App\Providers\AppServiceProvider;
use Src\Auth\Infrastructure\Providers\UserServiceProvider;
use Src\Blog\Comment\Infrastructure\Providers\CommentServiceProvider;

return [
    AppServiceProvider::class,
    UserServiceProvider::class,
    CommentServiceProvider::class
];
