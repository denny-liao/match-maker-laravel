<?php

namespace Src\Blog\Comment\Application\CommandHandlers;

use Src\Blog\Comment\Application\Commands\PostCommentCommand;
use Src\Blog\Comment\Domain\Entities\Comment;
use Src\Payment\Application\Commands\CreatePaymentCommand;
use Src\Payment\Application\Contracts\CreatePaymentHandlerInterface;
use Src\Payment\Application\DTOs\PaymentFormDto;

final readonly class DonateCommentHandler
{
    public function __construct(
        private readonly PostCommentAction $action,
        private readonly CreatePaymentHandlerInterface $createPaymentHandler,
    ) {}

    public function handle(PostCommentCommand $command, string $channel): string
    {
        $comment = $this->action->handle($command);

        $tradeNo = $this->createPaymentHandler->handle(new CreatePaymentCommand(
            referenceId: $comment->id,
            amount: $comment->amount,
            channel: $channel,
            itemName: '站內留言',
            tradeDesc: '數位工具開發與技術服務支持',
            userId: $comment->authorId
        ));
        
        return $tradeNo;
    }
}
