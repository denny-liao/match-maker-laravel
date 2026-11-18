<?php

use Illuminate\Support\Facades\Broadcast;
use Src\Auth\Infrastructure\EloquentModels\UserEloquentModel;
use Src\Matchmaking\Room\Infrastructure\EloquentModels\RoomEloquentModel;
use Src\Matchmaking\Room\Infrastructure\EloquentModels\RoomRecordEloquentModel;

// laravel 預設範例，私人推播 PrivateChannel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('matchmaking.lobby', fn (UserEloquentModel $user): bool => true);

Broadcast::channel('rooms.{code}', function (UserEloquentModel $user, string $code): array|bool {
    $room = RoomEloquentModel::query()
        ->where('code', $code)
        ->whereIn('status', ['waiting', 'numbering', 'voting'])
        ->first();

    if ($room === null) {
        return false;
    }

    $isParticipant = RoomRecordEloquentModel::query()
        ->where('room_id', $room->id)
        ->where('user_id', $user->id)
        ->whereNull('left_at')
        ->exists();

    if (! $isParticipant) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
