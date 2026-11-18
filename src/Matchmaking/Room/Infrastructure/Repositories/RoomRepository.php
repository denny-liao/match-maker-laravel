<?php

namespace Src\Matchmaking\Room\Infrastructure\Repositories;

use Closure;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Src\Matchmaking\Room\Domain\Entities\Participant;
use Src\Matchmaking\Room\Domain\Entities\Room;
use Src\Matchmaking\Room\Domain\Enums\RoomClosureReason;
use Src\Matchmaking\Room\Domain\Enums\RoomStatus;
use Src\Matchmaking\Room\Domain\Repositories\RoomRepositoryInterface;
use Src\Matchmaking\Room\Infrastructure\EloquentModels\RoomEloquentModel;
use Src\Matchmaking\Room\Infrastructure\EloquentModels\RoomRecordEloquentModel;

final class RoomRepository implements RoomRepositoryInterface
{
    public function create(Room $room): Room
    {
        return DB::transaction(function () use ($room): Room {
            // 建立 Room 資料
            $model = RoomEloquentModel::query()->create([
                'code' => $room->code,
                'host_id' => $room->hostId,
                'title' => $room->title,
                'status' => $room->status->value,
                'closure_reason' => $room->closureReason?->value,
                'max_players' => $room->maxPlayers,
            ]);

            $room->id = $model->id;
            // 建立關聯資料 參與人
            foreach ($room->participants as $participant) {
                $record = $model->records()->create($this->participantAttributes($participant));
                $participant->recordId = $record->id;
            }

            return $this->toEntity($model->load($this->participantRelations()));
        });
    }

    public function listOpenRooms(): array
    {
        $rooms = RoomEloquentModel::query()
            ->where('status', RoomStatus::Waiting->value)
            ->withCount(['records as participant_count' => fn ($query) => $query->whereNull('left_at')])
            ->with($this->participantRelations())
            ->latest('id')
            ->get();

        return array_values(
            $rooms->map(fn (RoomEloquentModel $room): Room => $this->toEntity($room))->all(),
        );
    }

    public function findByCode(string $code): ?Room
    {
        $model = RoomEloquentModel::query()
            ->where('code', $code)
            ->with($this->participantRelations())
            ->first();

        return $model === null ? null : $this->toEntity($model);
    }
    // 改變房間狀態
    public function mutate(string $code, Closure $operation): Room
    {
        return DB::transaction(function () use ($code, $operation): Room {
            $model = RoomEloquentModel::query()
                ->where('code', $code)
                ->lockForUpdate()
                ->firstOrFail();
            $model->load($this->participantRelations());

            $room = $this->toEntity($model);
            $operation($room);

            $model->fill([
                'host_id' => $room->hostId,
                'status' => $room->status->value,
                'started_at' => $room->startedAt,
                'closed_at' => $room->closedAt,
                'closure_reason' => $room->closureReason?->value,
            ])->save();

            foreach ($room->participants as $participant) {
                $attributes = $this->participantAttributes($participant);
                // participant 未寫入資料庫 => 寫入 room 關聯資料
                if ($participant->recordId === null) {
                    $record = $model->records()->create($attributes);
                    $participant->recordId = $record->id;

                    continue;
                }
                // 資料庫已有資料，則寫入該房間的紀錄的 attributes
                $record = RoomRecordEloquentModel::query()
                    ->where('room_id', $model->id)
                    ->whereKey($participant->recordId)
                    ->firstOrFail();
                $record->fill($attributes)->save();
            }

            $freshModel = $model->refresh();

            return $this->toEntity($freshModel->load($this->participantRelations()));
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function participantAttributes(Participant $participant): array
    {
        return [
            'user_id' => $participant->userId,
            'role' => $participant->role,
            'status' => $participant->status,
            'participant_number' => $participant->participantNumber,
            'voted_user_ids' => $participant->votedUserIds,
            'left_at' => $participant->leftAt,
        ];
    }

    /**
     * @return array<string, Closure>
     */
    private function participantRelations(): array
    {
        return [
            'records' => fn ($query) => $query
                ->join('users', 'users.id', '=', 'room_records.user_id')
                ->addSelect('room_records.*', 'users.name as user_name')
                ->orderBy('room_records.id'),
        ];
    }

    private function toEntity(RoomEloquentModel $model): Room
    {
        $records = $model->records;

        return new Room(
            id: $model->id,
            code: $model->code,
            hostId: $model->host_id,
            title: $model->title,
            status: RoomStatus::from($model->status),
            maxPlayers: $model->max_players,
            participants: array_values($records->map(
                fn (RoomRecordEloquentModel $record): Participant => new Participant(
                    recordId: $record->id,
                    userId: $record->user_id,
                    name: $record->user_name,
                    role: $record->role,
                    status: $record->status,
                    participantNumber: $record->participant_number,
                    votedUserIds: $this->voteIds($record->voted_user_ids),
                    leftAt: $record->left_at === null
                        ? null
                        : new DateTimeImmutable((string) $record->left_at),
                ),
            )->all()),
            startedAt: $model->started_at === null
                ? null
                : new DateTimeImmutable((string) $model->started_at),
            closedAt: $model->closed_at === null
                ? null
                : new DateTimeImmutable((string) $model->closed_at),
            closureReason: $model->closure_reason === null
                ? null
                : RoomClosureReason::from($model->closure_reason),
        );
    }

    /**
     * @return list<int>|null
     */
    private function voteIds(mixed $voteIds): ?array
    {
        if (is_string($voteIds)) {
            $voteIds = json_decode($voteIds, true, flags: JSON_THROW_ON_ERROR);
        }

        if ($voteIds === null) {
            return null;
        }

        if (! is_array($voteIds)) {
            throw new \UnexpectedValueException('Stored participant votes must be a JSON array.');
        }

        return array_values(array_map('intval', $voteIds));
    }
}
