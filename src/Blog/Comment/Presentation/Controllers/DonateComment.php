<?php

namespace Src\Blog\Comment\Presentation\Controllers;

use Illuminate\Http\RedirectResponse;
use Src\Blog\Comment\Application\CommandHandlers\DonateCommentHandler;
use Src\Blog\Comment\Application\Commands\PostCommentCommand;
use Src\Blog\Comment\Presentation\Requests\DonateCommentRequest;
use Src\Common\Presentation\Controllers\Controller;

class DonateComment extends Controller
{
    public function __construct(
        public DonateCommentHandler $donateCommentHandler,
    ) {}

    public function __invoke(DonateCommentRequest $request): RedirectResponse
    {

        $command = new PostCommentCommand($request->user()->id, $request->message, $request->user()->name, $request->amount);

        $tradeNo = $this->donateCommentHandler->handle($command, $request->channel);
        return to_route('payments.info', [
            'trade_no' => $tradeNo
        ]);
    }
}
