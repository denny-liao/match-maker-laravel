export interface CommentItem {
    id: number;
    message: string;
    authorName: string;
    createdAt: string;
    avatar: string;
}

export interface CommentProps {
    comments?: CommentItem[];
}