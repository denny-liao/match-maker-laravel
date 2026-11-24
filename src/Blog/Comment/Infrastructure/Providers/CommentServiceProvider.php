<?php

namespace Src\Blog\Comment\Infrastructure\Providers;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use PDO;
use Src\Blog\Comment\Application\CommandHandlers\EditCommentHandler;
use Src\Blog\Comment\Application\Commands\EditCommentCommand;
use Src\Blog\Comment\Application\Listeners\PublishCommentOnPaymentCompleted;
use Src\Blog\Comment\Domain\Repositories\CommentRepositoryInterface;
use Src\Blog\Comment\Infrastructure\Repositories\CommentRepository;
use Src\Payment\Domain\Events\PaymentCompleted;

class CommentServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        CommentRepositoryInterface::class => CommentRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PDO::class, function ($app) {
            // 確保在 CQRS 查詢時取得唯讀連線
            return $app['db']->connection()->getReadPdo();
        });
    }

    public function boot(): void
    {
        Bus::map([
            EditCommentCommand::class => EditCommentHandler::class,
        ]);

        Event::listen(PaymentCompleted::class, PublishCommentOnPaymentCompleted::class);
    }
}
