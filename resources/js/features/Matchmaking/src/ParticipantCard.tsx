import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getRandomAvatar } from '@/lib/utils';
import type { RoomParticipant } from './types';

type ParticipantCardProps = {
    participant: RoomParticipant;
    roomStatus: string;
    isCurrentUser: boolean;
};

export default function ParticipantCard({
    participant,
    roomStatus,
    isCurrentUser,
}: ParticipantCardProps) {
    const initials = participant.name.trim().slice(0, 1) || '?';

    return (
        <article className="flex w-36 shrink-0 animate-in flex-col items-center gap-3 rounded-2xl border bg-card p-4 text-center shadow-sm transition-all duration-500 zoom-in-90 sm:w-44 sm:p-5">
            <Avatar
                size="lg"
                className="size-16 bg-primary/10 text-lg font-semibold text-primary"
            >
                <AvatarImage
                    src={getRandomAvatar(participant.name)}
                    alt={`${participant.name} 的頭像`}
                />
                <AvatarFallback>{initials}</AvatarFallback>
            </Avatar>
            <div className="max-w-full min-w-0">
                <p className="truncate font-medium">
                    {participant.name}
                    {isCurrentUser && (
                        <span className="ml-1 text-xs text-muted-foreground">
                            (你)
                        </span>
                    )}
                </p>
                <p className="mt-1 text-xs text-muted-foreground">
                    {participant.role === 'host' ? '房主' : '參與者'}
                </p>
            </div>
            {roomStatus === 'waiting' && (
                <span
                    className={`rounded-full px-2.5 py-1 text-xs ${
                        participant.status === 'ready'
                            ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
                            : 'bg-muted text-muted-foreground'
                    }`}
                >
                    {participant.status === 'ready' ? '已準備' : '準備中'}
                </span>
            )}
            {roomStatus === 'numbering' && (
                <span className="rounded-full bg-muted px-2.5 py-1 text-xs text-muted-foreground">
                    {participant.participantNumber === null
                        ? '輸入編號中'
                        : '已輸入編號'}
                </span>
            )}
            {roomStatus === 'voting' && (
                <span className="rounded-full bg-muted px-2.5 py-1 text-xs text-muted-foreground">
                    {participant.hasVoted ? '已完成投票' : '投票中'}
                </span>
            )}
        </article>
    );
}
