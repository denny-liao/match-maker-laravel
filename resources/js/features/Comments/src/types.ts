export interface CommentItem {
    id: number;
    message: string;
    authorId: number;
    authorName: string;
    createdAt: string;
    avatar: string;
}

export interface CommentProps {
    comments?: CommentItem[];
}

export type Comment = Pick<CommentItem, 'id' | 'message'>;

export interface EditCommentProps {
    comment: Comment;
}
