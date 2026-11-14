<?php

namespace Src\Blog\Comment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use PDO;
use Src\Blog\Comment\Domain\Repositories\CommentRepositoryInterface;
use Src\Blog\Comment\Infrastructure\Repositories\CommentRepository;

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
}
