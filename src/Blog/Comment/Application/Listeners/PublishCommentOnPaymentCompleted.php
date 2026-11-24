<?php

namespace Src\Blog\Comment\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Bus;
use Src\Blog\Comment\Application\Commands\EditCommentCommand;
use Src\Payment\Domain\Events\PaymentCompleted;

class PublishCommentOnPaymentCompleted implements ShouldQueue, ShouldQueueAfterCommit
{
    public function handle(PaymentCompleted $event): void
    {
        Bus::dispatch(new EditCommentCommand(
            id: $event->referenceId,
            amount: $event->amount
        )); 
    }
}
