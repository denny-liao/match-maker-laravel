import { useConnectionStatus, useEchoPresence } from '@laravel/echo-react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Check, DoorOpen, Heart } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { ParticipantCard } from '@/features/Matchmaking';
import type {
    MatchmakingRoom,
    RoomStateChangedPayload,
} from '@/features/Matchmaking';

type RoomPageProps = {
    room: MatchmakingRoom;
    currentUserId: number;
};

function RoomPresence({
    roomCode,
    onRoomUpdated,
    onConnectionId,
}: {
    roomCode: string;
    onRoomUpdated: (room: MatchmakingRoom) => void;
    onConnectionId: (connectionId: string) => void;
}) {
    const [connectionId, setConnectionId] = useState<string | null>(null);
    const [connectionError, setConnectionError] = useState(false);

    useEffect(() => {
        let cancelled = false;
        const id = crypto.randomUUID();
        onConnectionId(id);

        async function reconnect() {
            const csrfToken = document.querySelector<HTMLMetaElement>(
                'meta[name="csrf-token"]',
            )?.content;

            if (!csrfToken) {
                throw new Error('無法取得 CSRF token，無法重新連線房間。');
            }

            const response = await fetch(
                `/matchmaking/rooms/${roomCode}/reconnect`,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ connection_id: id }),
                },
            );

            if (!response.ok) {
                throw new Error(`房間重新連線失敗：HTTP ${response.status}`);
            }

            if (!cancelled) {
                setConnectionId(id);
            }
        }

        void reconnect().catch((error: unknown) => {
            if (!cancelled) {
                console.error(error);
                setConnectionError(true);
            }
        });

        return () => {
            cancelled = true;
        };
    }, [onConnectionId, roomCode]);

    if (connectionError) {
        return (
            <p className="mt-3 text-xs text-destructive" role="alert">
                房間連線失敗，請重新整理頁面再試。
            </p>
        );
    }

    if (!connectionId) {
        return (
            <p
                className="mt-3 text-xs text-muted-foreground"
                aria-live="polite"
            >
                房間連線中…
            </p>
        );
    }

    return (
        <RoomPresenceSubscription
            roomCode={roomCode}
            onRoomUpdated={onRoomUpdated}
        />
    );
}

function RoomPresenceSubscription({
    roomCode,
    onRoomUpdated,
}: {
    roomCode: string;
    onRoomUpdated: (room: MatchmakingRoom) => void;
}) {
    useEchoPresence<RoomStateChangedPayload>(
        `rooms.${roomCode}`,
        '.RoomStateChanged',
        ({ room }) => onRoomUpdated(room),
    );
    const connectionStatus = useConnectionStatus();

    return (
        <p className="mt-3 text-xs text-muted-foreground" aria-live="polite">
            即時連線：{connectionStatus === 'connected' ? '已連線' : '連線中'}
        </p>
    );
}

export default function RoomPage({
    room: initialRoom,
    currentUserId,
}: RoomPageProps) {
    const [room, setRoom] = useState(initialRoom);
    const [selectedUserIds, setSelectedUserIds] = useState<number[]>([]);
    const [actionPending, setActionPending] = useState(false);
    const actionPendingRef = useRef(false);
    const isLeavingRef = useRef(false);
    const leaveBeaconSentRef = useRef(false);
    const connectionIdRef = useRef<string | null>(null);
    const { props } = usePage<{ errors?: { room?: string } }>();
    const numberForm = useForm({ number: '' });
    const voteForm = useForm<{ target_user_ids: number[] }>({
        target_user_ids: [],
    });

    useEffect(() => {
        setRoom(initialRoom);
    }, [initialRoom]);

    const setConnectionId = useMemo(
        () => (connectionId: string) => {
            connectionIdRef.current = connectionId;
            leaveBeaconSentRef.current = false;
        },
        [],
    );

    useEffect(() => {
        function notifyRoomDisconnect() {
            const connectionId = connectionIdRef.current;

            if (
                isLeavingRef.current ||
                leaveBeaconSentRef.current ||
                connectionId === null
            ) {
                return;
            }

            leaveBeaconSentRef.current = true;
            const csrfToken = document.querySelector<HTMLMetaElement>(
                'meta[name="csrf-token"]',
            )?.content;

            if (!csrfToken) {
                console.error('無法取得 CSRF token，未能通知伺服器離開房間。');
                return;
            }

            const body = new URLSearchParams({
                _token: csrfToken,
                connection_id: connectionId,
            });
            const url = `/matchmaking/rooms/${room.code}/disconnect`;
            // sendBeacon: 離開前背景非同步發送
            if (!navigator.sendBeacon(url, body)) {
                void fetch(url, {
                    method: 'POST',
                    body,
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                    keepalive: true, // 同 sendBeacon 機制
                }).catch((error: unknown) => {
                    console.error('離開房間通知失敗。', error);
                });
            }
        }
        // 攔截請求，Link、router跳轉、上下頁觸發
        const removeBeforeListener = router.on('before', (event) => {
            const visit = event.detail.visit;

            if (
                visit.method === 'get' &&
                visit.url.pathname !== window.location.pathname
            ) {
                notifyRoomDisconnect();
            }
        });

        function returnToLobby(event: PopStateEvent) {
            notifyRoomDisconnect();
            event.stopImmediatePropagation();
            router.visit('/matchmaking', { replace: true });
        }

        window.addEventListener('pagehide', notifyRoomDisconnect);
        window.addEventListener('beforeunload', notifyRoomDisconnect);
        window.addEventListener('popstate', returnToLobby, true);

        return () => {
            removeBeforeListener();
            window.removeEventListener('pagehide', notifyRoomDisconnect);
            window.removeEventListener('beforeunload', notifyRoomDisconnect);
            window.removeEventListener('popstate', returnToLobby, true);
        };
    }, [room.code, room.status]);

    const currentParticipant = useMemo(
        () =>
            room.participants.find(
                (participant) => participant.userId === currentUserId,
            ),
        [currentUserId, room.participants],
    );
    const isHost = room.hostId === currentUserId;
    const allReady =
        room.participantCount >= 2 && room.readyCount === room.participantCount;
    const canJoinPresence =
        room.status !== 'closed' && currentParticipant !== undefined;

    useEffect(() => {
        if (
            currentParticipant?.participantNumber !== null &&
            currentParticipant?.participantNumber !== undefined
        ) {
            numberForm.setData(
                'number',
                String(currentParticipant.participantNumber),
            );
        }
    }, [currentParticipant?.participantNumber]);

    function assignNumber(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();
        numberForm.post(`/matchmaking/rooms/${room.code}/number`, {
            showProgress: false,
        });
    }

    function submitVote(event: React.SubmitEvent<HTMLFormElement>) {
        event.preventDefault();
        voteForm.transform(() => ({ target_user_ids: selectedUserIds }));
        voteForm.post(`/matchmaking/rooms/${room.code}/vote`, {
            showProgress: false,
        });
    }
    // 準備 或 開始配對
    function postRoomAction(action: 'ready' | 'start') {
        if (actionPendingRef.current) {
            return;
        }

        actionPendingRef.current = true;
        setActionPending(true);

        router.post(
            `/matchmaking/rooms/${room.code}/${action}`,
            {},
            {
                showProgress: false,
                onFinish: () => {
                    actionPendingRef.current = false;
                    setActionPending(false);
                },
            },
        );
    }

    function leaveRoom() {
        if (actionPendingRef.current) {
            return;
        }

        actionPendingRef.current = true;
        isLeavingRef.current = true;
        setActionPending(true);

        router.post(
            `/matchmaking/rooms/${room.code}/leave`,
            {},
            {
                showProgress: false,
                onError: () => {
                    isLeavingRef.current = false;
                },
                onFinish: () => {
                    actionPendingRef.current = false;
                    setActionPending(false);
                },
            },
        );
    }

    function toggleVote(userId: number, checked: boolean) {
        setSelectedUserIds((current) =>
            checked
                ? [...current, userId]
                : current.filter((id) => id !== userId),
        );
    }

    return (
        <>
            <Head title={room.title} />
            <div className="mx-auto max-w-5xl space-y-8">
                <section className="rounded-2xl border bg-card p-6 text-center shadow-sm sm:p-8">
                    <p className="text-sm font-medium text-primary">
                        配對房間 · {room.code}
                    </p>
                    <h1 className="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">
                        {room.title}
                    </h1>
                    <p className="mt-2 text-sm text-muted-foreground">
                        {room.status === 'waiting' && '等待參與者加入並準備好'}
                        {room.status === 'numbering' &&
                            '請每位參與者輸入自己的編號'}
                        {room.status === 'voting' &&
                            '選擇想配對的對象，可複選或留空'}
                        {room.status === 'closed' && '本次配對已完成'}
                    </p>
                    {room.status !== 'closed' && (
                        <Button
                            className="mt-4"
                            variant="outline"
                            onClick={leaveRoom}
                            disabled={actionPending}
                        >
                            <DoorOpen className="size-4" />
                            {actionPending ? '處理中…' : '退出房間'}
                        </Button>
                    )}
                    {canJoinPresence && (
                        <RoomPresence
                            roomCode={room.code}
                            onRoomUpdated={setRoom}
                            onConnectionId={setConnectionId}
                        />
                    )}
                </section>
                {props.errors?.room && (
                    <p
                        role="alert"
                        className="text-center text-sm text-destructive"
                    >
                        {props.errors.room}
                    </p>
                )}

                <section className="space-y-5">
                    <div className="flex flex-col gap-1 text-center">
                        <h2 className="text-lg font-semibold">
                            參與者 {room.participantCount}/{room.maxPlayers}
                        </h2>
                        {room.status === 'waiting' && (
                            <p className="text-sm text-muted-foreground">
                                {room.readyCount}/{room.participantCount}{' '}
                                位已準備
                            </p>
                        )}
                        {room.status === 'numbering' && (
                            <p className="text-sm text-muted-foreground">
                                {room.numberedCount}/{room.participantCount}{' '}
                                位已輸入編號
                            </p>
                        )}
                        {room.status === 'voting' && (
                            <p className="text-sm text-muted-foreground">
                                {room.votedCount}/{room.participantCount}{' '}
                                位已完成投票
                            </p>
                        )}
                    </div>

                    <div className="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                        {room.participants.map((participant) => (
                            <ParticipantCard
                                key={participant.userId}
                                participant={participant}
                                roomStatus={room.status}
                                isCurrentUser={
                                    participant.userId === currentUserId
                                }
                            />
                        ))}
                    </div>
                </section>

                {room.status === 'waiting' && currentParticipant && (
                    <section className="flex flex-col items-center gap-4">
                        <Button
                            disabled={actionPending}
                            variant={
                                currentParticipant.status === 'ready'
                                    ? 'secondary'
                                    : 'default'
                            }
                            onClick={() => postRoomAction('ready')}
                        >
                            {currentParticipant.status === 'ready' ? (
                                <>
                                    <Check className="size-4" />
                                    已準備，點擊取消
                                </>
                            ) : (
                                '我準備好了'
                            )}
                        </Button>
                        {isHost && (
                            <Button
                                disabled={!allReady || actionPending}
                                onClick={() => postRoomAction('start')}
                            >
                                開始配對
                            </Button>
                        )}
                    </section>
                )}

                {room.status === 'numbering' && currentParticipant && (
                    <form
                        onSubmit={assignNumber}
                        className="mx-auto flex max-w-sm flex-col gap-3"
                    >
                        <label
                            htmlFor="participant-number"
                            className="text-center text-sm font-medium"
                        >
                            輸入你的編號（1 至 {room.participantCount}）
                        </label>
                        <Input
                            id="participant-number"
                            type="number"
                            min={1}
                            max={room.participantCount}
                            required
                            value={numberForm.data.number}
                            onChange={(event) =>
                                numberForm.setData('number', event.target.value)
                            }
                            aria-invalid={Boolean(numberForm.errors.number)}
                        />
                        {numberForm.errors.number && (
                            <p className="text-sm text-destructive">
                                {numberForm.errors.number}
                            </p>
                        )}
                        <Button type="submit" disabled={numberForm.processing}>
                            {numberForm.processing ? '送出中…' : '確認編號'}
                        </Button>
                    </form>
                )}

                {room.status === 'voting' && currentParticipant && (
                    <form
                        onSubmit={submitVote}
                        className="mx-auto max-w-2xl space-y-5"
                    >
                        <div className="space-y-3">
                            {room.participants
                                .filter(
                                    (participant) =>
                                        participant.userId !== currentUserId,
                                )
                                .map((participant) => (
                                    <label
                                        key={participant.userId}
                                        className="flex cursor-pointer items-center gap-3 rounded-xl border bg-card p-4 transition-colors hover:bg-accent/50 has-[:checked]:border-primary"
                                    >
                                        <Checkbox
                                            checked={selectedUserIds.includes(
                                                participant.userId,
                                            )}
                                            onCheckedChange={(checked) =>
                                                toggleVote(
                                                    participant.userId,
                                                    checked === true,
                                                )
                                            }
                                            disabled={
                                                currentParticipant.hasVoted
                                            }
                                        />
                                        <span className="flex-1 font-medium">
                                            {participant.name}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            編號 {participant.participantNumber}
                                        </span>
                                    </label>
                                ))}
                        </div>
                        <div className="flex flex-col items-center gap-3">
                            <p className="text-sm text-muted-foreground">
                                可複選；不選任何人並送出即代表留空。
                            </p>
                            <Button
                                type="submit"
                                disabled={
                                    currentParticipant.hasVoted ||
                                    voteForm.processing
                                }
                            >
                                <Heart className="size-4" />
                                {currentParticipant.hasVoted
                                    ? '已完成投票，等待其他人'
                                    : voteForm.processing
                                      ? '送出中…'
                                      : '送出投票'}
                            </Button>
                        </div>
                    </form>
                )}
            </div>

            <AlertDialog open={room.status === 'closed'}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {room.closureReason === 'participants_left'
                                ? '配對已結束'
                                : '配對結果已揭曉'}
                        </AlertDialogTitle>
                        <AlertDialogDescription className="space-y-2">
                            <span className="block">
                                {room.closureReason === 'participants_left'
                                    ? '其他參與者已離開，房間已結束配對。'
                                    : room.matches.length > 0
                                      ? `恭喜找到 ${room.matches.length} 組互相選擇的配對！`
                                      : '這次沒有互相選擇的配對，歡迎下次再試。'}
                            </span>
                            {room.closureReason !== 'participants_left' &&
                                room.matches.map((match, index) => (
                                    <span
                                        key={`${match.first.number}-${match.second.number}-${index}`}
                                        className="block font-medium text-foreground"
                                    >
                                        {match.first.number ?? '未編號'} 號{' '}
                                        {match.first.name} ♥{' '}
                                        {match.second.number ?? '未編號'} 號{' '}
                                        {match.second.name}
                                    </span>
                                ))}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <Button disabled={actionPending} onClick={leaveRoom}>
                            {actionPending
                                ? '處理中…'
                                : '退出房間並回到配對大廳'}
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
