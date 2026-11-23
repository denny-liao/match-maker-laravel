<?php

namespace Src\Payment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Src\Payment\Infrastructure\Repositories\PaymentRepository;

final class PaymentServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        PaymentRepositoryInterface::class => PaymentRepository::class,
    ];
}
