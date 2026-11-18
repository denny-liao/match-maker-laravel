export type RoomParticipant = {
    userId: number;
    name: string;
    role: 'host' | 'player';
    status: 'unready' | 'ready';
    participantNumber: number | null;
    hasVoted: boolean;
};

export type MatchResult = {
    first: { number: number | null; name: string };
    second: { number: number | null; name: string };
};

export type MatchmakingRoom = {
    id: number | null;
    code: string;
    title: string;
    hostId: number;
    status: 'waiting' | 'numbering' | 'voting' | 'closed';
    closureReason: 'completed' | 'participants_left' | null;
    maxPlayers: number;
    participantCount: number;
    readyCount: number;
    numberedCount: number;
    votedCount: number;
    participants: RoomParticipant[];
    matches: MatchResult[];
};

export type RoomStateChangedPayload = {
    room: MatchmakingRoom;
};

export type RoomLobbyUpdatedPayload = {
    room: MatchmakingRoom;
};
