<?php

namespace Src\Matchmaking\Room\Infrastructure\Services;

use Illuminate\Support\Facades\Cache;
use Src\Matchmaking\Room\Application\CommandHandlers\LeaveRoomHandler;
use Src\Matchmaking\Room\Application\Commands\LeaveRoomCommand;

final class RoomParticipantDisconnect
{
    // 快取用於驗證離房通知
    public function reconnect(string $code, int $userId, string $connectionId): void
    {
        Cache::put(
            $this->connectionKey($code, $userId),
            $connectionId,
            now()->addHours(2),
        );
    }

    public function disconnect(string $code, int $userId, string $connectionId): void
    {
        // 已經 disconnected
        if (Cache::get($this->connectionKey($code, $userId)) !== $connectionId) {
            return;
        }
        // 移除 快取 => disconnect
        Cache::forget($this->connectionKey($code, $userId));
        // 服務容器解析
        app(LeaveRoomHandler::class)->handle(new LeaveRoomCommand(
            code: $code,
            userId: $userId,
        ));
    }

    private function connectionKey(string $code, int $userId): string
    {
        return "matchmaking:room:{$code}:participant:{$userId}:connection";
    }
}
