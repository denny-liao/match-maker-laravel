<?php

use Src\App\Providers\AppServiceProvider;
use Src\Auth\Infrastructure\Providers\FortifyServiceProvider;
use Src\Auth\Infrastructure\Providers\UserServiceProvider;
use Src\Blog\Comment\Infrastructure\Providers\CommentServiceProvider;
use Src\Common\Infrastructure\Providers\CommonServiceProvider;
use Src\Matchmaking\Room\Infrastructure\Providers\RoomServiceProvider;
use Src\Payment\Infrastructure\Providers\PaymentServiceProvider;

return [
    FortifyServiceProvider::class,
    AppServiceProvider::class,
    UserServiceProvider::class,
    CommentServiceProvider::class,
    RoomServiceProvider::class,
    PaymentServiceProvider::class,
    CommonServiceProvider::class
];
