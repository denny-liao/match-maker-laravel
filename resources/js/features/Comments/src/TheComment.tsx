import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { CommentItem } from './types';
import { cn, getRandomAvatar } from '@/lib/utils';
import { Button, buttonVariants } from '@/components/ui/button';
import { Pencil, Trash2 } from 'lucide-react';
import { ConfirmDialog } from '@/components/Dialog';
import { DialogEnum } from '@/enums/appEnum';
import { Link, router } from '@inertiajs/react';
import { useAuth } from '@/hooks/useAuth';


export default function TheComment({ comment }: { comment: CommentItem }) {
    const { user, isAuthenticated } = useAuth()


    // 刪除處理函式
    const handleDelete = (commentId: number) => {
        router.delete(`/comments/${commentId}`, {
            preserveScroll: true,
        });
    };
    return (
        <div
            className="flex gap-4 rounded-xl border bg-card p-5 shadow-xs transition-all hover:bg-accent/20"
        >
            {/* 匿名頭像 */}
            <Avatar className="size-10 shrink-0 border">
                <AvatarImage
                    src={getRandomAvatar(comment.authorName)}
                    alt={comment.authorName}
                />
                <AvatarFallback>
                    {comment.authorName.slice(0, 1)}
                </AvatarFallback>
            </Avatar>

            {/* 留言主體內容 */}
            <div className="min-w-0 flex-1">
                <div className="flex items-center justify-between gap-2">
                    <div className="flex items-baseline gap-2 truncate">
                        <span className="truncate font-semibold text-foreground">
                            {comment.authorName}
                        </span>
                        <span
                            className="shrink-0 text-xs text-muted-foreground"
                            title={comment.createdAt}
                        >
                            {comment.createdAt}
                        </span>
                    </div>

                    {/* 編輯與刪除功能按鈕 */}
                    {isAuthenticated && user.id === comment.authorId
                        && <div className="flex items-center gap-1">
                            <Link
                                href={`/comments/${comment.id}/edit`}
                                className={cn(
                                    buttonVariants({ variant: "ghost", size: "icon" }),
                                    "size-7 text-muted-foreground hover:text-foreground"
                                )}
                            >
                                <Pencil className="size-3.5" />
                                <span className="sr-only">編輯</span>
                            </Link>
                            {/* 刪除按鈕：外部傳入 DELETE 路由事件 */}
                            <ConfirmDialog
                                type={DialogEnum.DELETE}
                                onConfirm={() => handleDelete(comment.id)}
                            />
                        </div>}
                </div>

                <p className="mt-2 whitespace-pre-wrap break-words text-sm leading-relaxed text-foreground/90">
                    {comment.message}
                </p>
            </div>

        </div>
    )
}