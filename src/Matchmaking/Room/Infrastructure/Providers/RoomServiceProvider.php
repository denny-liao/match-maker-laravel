<?php

namespace Src\Matchmaking\Room\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;
use Src\Matchmaking\Room\Infrastructure\Repositories\RoomRepository;

final class RoomServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        RoomRepositoryInterface::class => RoomRepository::class,
    ];
}
