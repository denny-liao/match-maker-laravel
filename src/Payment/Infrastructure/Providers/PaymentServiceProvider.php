<?php

namespace Src\Payment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Payment\Application\CommandHandlers\CreatePaymentHandler;
use Src\Payment\Application\Contracts\CreatePaymentHandlerInterface;
use Src\Payment\Domain\Adapters\PaymentAdapterInterface;
use Src\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Src\Payment\Infrastructure\Adapters\EcpayAdapter;
use Src\Payment\Infrastructure\Repositories\PaymentRepository;

final class PaymentServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        PaymentRepositoryInterface::class => PaymentRepository::class,
        PaymentAdapterInterface::class => EcpayAdapter::class,
        CreatePaymentHandlerInterface::class => CreatePaymentHandler::class,
    ];
}
