<?php

namespace Src\Common\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Common\Domain\Clock\Clock;
use Src\Common\Infrastructure\Clock\TaipeiClock;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        Clock::class => TaipeiClock::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {}
}
