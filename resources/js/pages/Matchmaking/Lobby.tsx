import { useEcho } from '@laravel/echo-react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { DoorOpen, UsersRound } from 'lucide-react';
import { useEffect, useState } from 'react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import type {
    MatchmakingRoom,
    RoomLobbyUpdatedPayload,
} from '@/features/Matchmaking';

type LobbyProps = {
    rooms: MatchmakingRoom[];
    currentUserId: number;
};

export default function Lobby({
    rooms: initialRooms,
    currentUserId,
}: LobbyProps) {
    const [rooms, setRooms] = useState(initialRooms);
    const [createDialogOpen, setCreateDialogOpen] = useState(false);
    const [joiningCode, setJoiningCode] = useState<string | null>(null);
    const createForm = useForm({ title: '快速配對房間' });

    useEffect(() => {
        setRooms(initialRooms);
    }, [initialRooms]);

    useEcho<RoomLobbyUpdatedPayload>(
        'matchmaking.lobby',
        '.RoomLobbyUpdated',
        ({ room }) => {
            setRooms((currentRooms) => {
                const withoutUpdatedRoom = currentRooms.filter(
                    (item) => item.code !== room.code,
                );
                // 非等待 => 列表移除該房間
                if (room.status !== 'waiting') {
                    return withoutUpdatedRoom;
                }

                return [room, ...withoutUpdatedRoom];
            });
        },
    );

    function createRoom() {
        createForm.post('/matchmaking/rooms', {
            onSuccess: () => setCreateDialogOpen(false),
        });
    }

    function joinRoom(room: MatchmakingRoom) {
        setJoiningCode(room.code);
        router.post(
            `/matchmaking/rooms/${room.code}/join`,
            {},
            {
                onFinish: () => setJoiningCode(null),
            },
        );
    }

    return (
        <>
            <Head title="配對大廳" />
            <div className="mx-auto max-w-5xl space-y-8">
                <section className="flex flex-col justify-between gap-4 rounded-2xl border bg-card p-6 shadow-sm sm:flex-row sm:items-center sm:p-8">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight sm:text-3xl">
                            配對大廳
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            建立房間邀請大家加入，準備好後即可開始配對。
                        </p>
                    </div>
                    <Button onClick={() => setCreateDialogOpen(true)}>
                        <UsersRound className="size-4" />
                        建立房間
                    </Button>
                </section>

                <section className="space-y-4">
                    <div>
                        <h2 className="text-xl font-semibold">可加入的房間</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            選擇一間等待中的房間加入配對。
                        </p>
                    </div>

                    {rooms.length === 0 ? (
                        <Card className="border-dashed">
                            <CardContent className="flex flex-col items-center gap-3 py-12 text-center">
                                <UsersRound className="size-9 text-muted-foreground" />
                                <p className="font-medium">
                                    目前沒有開放中的房間
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    建立一間房間，邀請其他人一起配對。
                                </p>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {rooms.map((room) => {
                                const isParticipant = room.participants.some(
                                    (participant) =>
                                        participant.userId === currentUserId,
                                );
                                const host = room.participants.find(
                                    (participant) =>
                                        participant.userId === room.hostId,
                                );

                                return (
                                    <Card key={room.code}>
                                        <CardHeader>
                                            <CardTitle>{room.title}</CardTitle>
                                            <CardDescription>
                                                房主：{host?.name ?? '未知'}
                                                ・房號 {room.code}
                                            </CardDescription>
                                        </CardHeader>
                                        <CardContent className="flex items-center justify-between gap-3">
                                            <span className="text-sm text-muted-foreground">
                                                {room.participantCount}/
                                                {room.maxPlayers} 人
                                            </span>
                                            {isParticipant ? (
                                                <Button
                                                    size="sm"
                                                    render={
                                                        <Link
                                                            href={`/matchmaking/rooms/${room.code}`}
                                                        />
                                                    }
                                                >
                                                    返回房間
                                                </Button>
                                            ) : (
                                                <Button
                                                    size="sm"
                                                    disabled={
                                                        room.participantCount >=
                                                            room.maxPlayers ||
                                                        joiningCode !== null
                                                    }
                                                    onClick={() =>
                                                        joinRoom(room)
                                                    }
                                                >
                                                    <DoorOpen className="size-4" />
                                                    {joiningCode === room.code
                                                        ? '加入中…'
                                                        : '加入房間'}
                                                </Button>
                                            )}
                                        </CardContent>
                                    </Card>
                                );
                            })}
                        </div>
                    )}
                </section>
            </div>

            <AlertDialog
                open={createDialogOpen}
                onOpenChange={setCreateDialogOpen}
            >
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            確定建立配對房間嗎？
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            建立後其他使用者就能在大廳看到並加入。
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <label className="grid gap-2 text-sm font-medium">
                        房間名稱
                        <Input
                            value={createForm.data.title}
                            onChange={(event) =>
                                createForm.setData('title', event.target.value)
                            }
                            maxLength={100}
                            aria-invalid={Boolean(createForm.errors.title)}
                        />
                    </label>
                    {createForm.errors.title && (
                        <p className="text-sm text-destructive">
                            {createForm.errors.title}
                        </p>
                    )}
                    <AlertDialogFooter>
                        <AlertDialogCancel disabled={createForm.processing}>
                            取消
                        </AlertDialogCancel>
                        <AlertDialogAction
                            disabled={
                                createForm.processing ||
                                !createForm.data.title.trim()
                            }
                            onClick={createRoom}
                        >
                            {createForm.processing ? '建立中…' : '確認建立'}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
