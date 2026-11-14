export interface CommentItem {
    id: number;
    message: string;
    authorName: string;
    createdAt: string;
    avatarUrl: string;
}

export interface CommentProps {
    comments?: CommentItem[];
}