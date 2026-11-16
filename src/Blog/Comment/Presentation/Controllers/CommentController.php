<?php

declare(strict_types=1);

namespace Src\Blog\Comment\Presentation\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Blog\Comment\Application\CommandHandlers\EditCommentHandler;
use Src\Blog\Comment\Application\CommandHandlers\PostCommentHandler;
use Src\Blog\Comment\Application\Commands\EditCommentCommand;
use Src\Blog\Comment\Application\Commands\PostCommentCommand;
use Src\Blog\Comment\Application\Queries\FindCommentByIdQuery;
use Src\Blog\Comment\Application\Queries\ListCommentsByQuery;
use Src\Blog\Comment\Presentation\Requests\CommentRequest;
use Src\Common\Presentation\Controllers\Controller;

enum Page: string
{
    case CommentIndex = 'Comments/Index';
    case CommentEdit = 'Comments/Edit';
}
enum Route: string 
{
    case Home = 'home';
}

final class CommentController extends Controller
{
    public function __construct(
        // 在此注入 Application Service
        private readonly ListCommentsByQuery $listCommentsHandler,
        private readonly FindCommentByIdQuery $findCommentByIdQuery,
        private readonly PostCommentHandler $postCommentHandler,
        private readonly EditCommentHandler $editCommandHandler
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
        $command = new PostCommentCommand(auth()->user()->id, $request->message);

        $this->postCommentHandler->handle($command);
        return  Inertia::flash('toast', [
            'type' => 'success',
            'title' => 'success',
            'description' => '留言新增成功!'
        ])
            ->back();
    }

    public function show(string $commentId) {}

    public function edit(string $commentId) //: Response
    {

        $comment = $this->findCommentByIdQuery->handle((int) $commentId);

        return Inertia::render(Page::CommentEdit, [
            'comment' => $comment,
        ]);
    }

    public function update(CommentRequest $request, string $commentId): RedirectResponse
    {
        // 呼叫 Application 執行更新邏輯
        $command = new EditCommentCommand((int) $commentId, auth()->user()->id, $request->message);
        $this->editCommandHandler->handle($command);

        Inertia::flash('toast', [
            'type' => 'success',
            'title' => 'success',
            'description' => '留言更新成功!'
        ]);
        return to_route(Route::Home);
    }

    public function destroy(string $commentId) //: RedirectResponse
    {
        // 呼叫 Application 執行刪除邏輯

        // return to_route('comments.index')
        //     ->with('success', 'Comment 已刪除。');
    }
}
