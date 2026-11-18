<?php

namespace Src\Matchmaking\Room\Presentation\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Src\Matchmaking\Room\Application\CommandHandlers\AssignParticipantNumberHandler;
use Src\Matchmaking\Room\Application\CommandHandlers\CreateRoomHandler;
use Src\Matchmaking\Room\Application\CommandHandlers\JoinRoomHandler;
use Src\Matchmaking\Room\Application\CommandHandlers\LeaveRoomHandler;
use Src\Matchmaking\Room\Application\CommandHandlers\StartMatchingHandler;
use Src\Matchmaking\Room\Application\CommandHandlers\SubmitRoomVoteHandler;
use Src\Matchmaking\Room\Application\CommandHandlers\ToggleRoomReadyHandler;
use Src\Matchmaking\Room\Application\Commands\AssignParticipantNumberCommand;
use Src\Matchmaking\Room\Application\Commands\CreateRoomCommand;
use Src\Matchmaking\Room\Application\Commands\JoinRoomCommand;
use Src\Matchmaking\Room\Application\Commands\LeaveRoomCommand;
use Src\Matchmaking\Room\Application\Commands\StartMatchingCommand;
use Src\Matchmaking\Room\Application\Commands\SubmitRoomVoteCommand;
use Src\Matchmaking\Room\Application\Commands\ToggleRoomReadyCommand;
use Src\Matchmaking\Room\Application\Queries\FindRoomByCodeQuery;
use Src\Matchmaking\Room\Application\Queries\ListOpenRoomsQuery;
use Src\Matchmaking\Room\Infrastructure\Services\RoomParticipantDisconnect;
use Src\Matchmaking\Room\Presentation\Requests\AssignParticipantNumberRequest;
use Src\Matchmaking\Room\Presentation\Requests\CreateRoomRequest;
use Src\Matchmaking\Room\Presentation\Requests\SubmitRoomVoteRequest;

final class RoomController
{
    public function __construct(
        private readonly ListOpenRoomsQuery $listOpenRooms,
        private readonly FindRoomByCodeQuery $findRoomByCode,
        private readonly CreateRoomHandler $createRoom,
        private readonly JoinRoomHandler $joinRoom,
        private readonly LeaveRoomHandler $leaveRoom,
        private readonly RoomParticipantDisconnect $participantDisconnect,
        private readonly ToggleRoomReadyHandler $toggleReady,
        private readonly StartMatchingHandler $startMatching,
        private readonly AssignParticipantNumberHandler $assignNumber,
        private readonly SubmitRoomVoteHandler $submitVote,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Matchmaking/Lobby', [
            'rooms' => $this->listOpenRooms->handle(),
            'currentUserId' => (int) $request->user()->getAuthIdentifier(),
        ]);
    }

    public function show(Request $request, string $code): Response|RedirectResponse
    {
        $room = $this->findRoomByCode->handle($code);
        // X-Inertia 是否為inertia前端發出，無 header 表示用URL或重整進入房間
        if ($room === null || ! $request->header('X-Inertia')) {
            return to_route('matchmaking.index');
        }

        $userId = (int) $request->user()->getAuthIdentifier();
        $participantIds = array_column($room['participants'], 'userId');
        // 有先 join() 或 創建房間 store()
        $isParticipant = in_array($userId, $participantIds, true);

        if (! $isParticipant) {
            return to_route('matchmaking.index');
        }
        // 導到 Room 執行 reconnect 並 建立連線
        return Inertia::render('Matchmaking/Room', [
            'room' => $room,
            'currentUserId' => $userId,
        ]);
    }

    public function disconnect(Request $request, string $code): HttpResponse
    {
        $room = $this->findRoomByCode->handle($code);
        abort_if($room === null, 404);
        // 還在房間內，不能斷線 => 禁止存取
        abort_unless(
            in_array(
                (int) $request->user()->getAuthIdentifier(),
                array_column($room['participants'], 'userId'),
                true,
            ),
            403,
        );
        // 斷開連線
        $this->participantDisconnect->disconnect(
            $code,
            (int) $request->user()->getAuthIdentifier(),
            $request->validate(['connection_id' => 'required|uuid'])['connection_id'],
        );

        return response()->noContent();
    }

    public function reconnect(Request $request, string $code): HttpResponse
    {
        $room = $this->findRoomByCode->handle($code);
        abort_if($room === null, 404);
        $userId = (int) $request->user()->getAuthIdentifier();
        abort_unless(in_array($userId, array_column($room['participants'], 'userId'), true), 403);

        $this->participantDisconnect->reconnect(
            $code,
            $userId,
            $request->validate(['connection_id' => 'required|uuid'])['connection_id'],
        );

        return response()->noContent();
    }
    // 創建房間
    public function store(CreateRoomRequest $request): RedirectResponse
    {
        $user = $request->user();
        $room = $this->createRoom->handle(new CreateRoomCommand(
            userId: (int) $user->getAuthIdentifier(),
            userName: $user->name,
            title: $request->validated('title'),
        ));
        // 跳轉
        return to_route('matchmaking.rooms.show', ['code' => $room->code]);
    }
    // 加入房間
    public function join(Request $request, string $code): RedirectResponse
    {
        $user = $request->user();
        $this->joinRoom->handle(new JoinRoomCommand(
            code: $code,
            userId: (int) $user->getAuthIdentifier(),
            userName: $user->name,
        ));

        return to_route('matchmaking.rooms.show', ['code' => $code]);
    }
    // 參與者離開房間
    public function leave(Request $request, string $code): RedirectResponse
    {
        $this->leaveRoom->handle(new LeaveRoomCommand(
            code: $code,
            userId: (int) $request->user()->getAuthIdentifier(),
        ));

        return to_route('matchmaking.index');
    }
    // 參與者準備完成
    public function toggleReady(Request $request, string $code): RedirectResponse
    {
        $this->toggleReady->handle(new ToggleRoomReadyCommand(
            code: $code,
            userId: (int) $request->user()->getAuthIdentifier(),
        ));

        return back();
    }
    // 開始遊戲
    public function start(Request $request, string $code): RedirectResponse
    {
        $this->startMatching->handle(new StartMatchingCommand(
            code: $code,
            userId: (int) $request->user()->getAuthIdentifier(),
        ));

        return back();
    }
    // 編號流程
    public function assignNumber(
        AssignParticipantNumberRequest $request,
        string $code,
    ): RedirectResponse {
        $this->assignNumber->handle(new AssignParticipantNumberCommand(
            code: $code,
            userId: (int) $request->user()->getAuthIdentifier(),
            number: (int) $request->validated('number'),
        ));

        return back();
    }
    // 投票配對
    public function vote(SubmitRoomVoteRequest $request, string $code): RedirectResponse
    {
        $this->submitVote->handle(new SubmitRoomVoteCommand(
            code: $code,
            userId: (int) $request->user()->getAuthIdentifier(),
            targetUserIds: array_values(array_map(
                'intval', // PHP intval function，強制轉型成 (int) 
                $request->validated('target_user_ids', []),
            )),
        ));

        return back();
    }
}
