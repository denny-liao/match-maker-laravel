<?php

declare(strict_types=1);

namespace Src\Blog\Comment\Presentation\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Blog\Comment\Application\CommandHandlers\PostCommentHandler;
use Src\Blog\Comment\Application\Commands\PostCommentCommand;
use Src\Blog\Comment\Application\Queries\ListCommentsByQuery;
use Src\Blog\Comment\Presentation\Requests\CommentRequest;
use Src\Common\Presentation\Controllers\Controller;

enum Page: string
{
    case CommentIndex = 'Comments/Index';
}

final class CommentController extends Controller
{
    public function __construct(
        // 在此注入 Application Service
        private readonly ListCommentsByQuery $listCommentsHandler,
        private readonly PostCommentHandler $postCommentHandler
    ) {}

    public function index(): Response
    {
        $comments = $this->listCommentsHandler->handle();
        return Inertia::render(Page::CommentIndex, [
            'comments' => $comments,
        ]);
    }

    public function create() {}

    public function store(CommentRequest $request): RedirectResponse
    {
        // 呼叫 Application 執行建立邏輯
        // $command = new PostCommentCommand(auth()->user()->id, $request->message);
        $command = new PostCommentCommand(null, $request->message);

        $this->postCommentHandler->handle($command);
        return  Inertia::flash('toast', [
            'type' => 'success',
            'title' => 'success',
            'description' => 'Your comment has been posted!'
        ])
            ->back();
    }

    public function show(string $commentId) {}

    public function edit(string $commentId) //: Response
    {
        // return Inertia::render('Blog/Comment/Edit', [
        //     'comment' => [
        //         'id' => $commentId,
        //     ],
        // ]);
    }

    public function update(Request $request, string $commentId) //: RedirectResponse
    {
        // 呼叫 Application 執行更新邏輯

        // return to_route('comments.show', $commentId)
        //     ->with('success', 'Comment 更新成功。');
    }

    public function destroy(string $commentId) //: RedirectResponse
    {
        // 呼叫 Application 執行刪除邏輯

        // return to_route('comments.index')
        //     ->with('success', 'Comment 已刪除。');
    }
}
