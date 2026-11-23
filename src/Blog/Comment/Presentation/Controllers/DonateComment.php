<?php

namespace Src\Blog\Comment\Presentation\Controllers;

use Inertia\Inertia;
use Src\Blog\Comment\Application\CommandHandlers\DonateCommentHandler;
use Src\Blog\Comment\Application\Commands\PostCommentCommand;
use Src\Blog\Comment\Presentation\Requests\DonateCommentRequest;
use Src\Common\Presentation\Controllers\Controller;

class DonateComment extends Controller
{
    public function __construct(
        public DonateCommentHandler $donateCommentHandler,
    ) {}

    public function __invoke(DonateCommentRequest $request)
    {

        $command = new PostCommentCommand($request->user()->id, $request->message, $request->user()->name, $request->amount);

        $paymentInfo = $this->donateCommentHandler->handle($command, $request->channel);
        return Inertia::render(Page::CommentIndex, [
            'info' => $paymentInfo,
        ]);
    }
}
