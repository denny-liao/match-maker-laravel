import { Head } from '@inertiajs/react';
import { PostCommentTextarea, TheComment, type CommentProps } from '@/features/Comments';
import { Fragment } from 'react';

// 未來開啟編輯、刪除功能時可取消註解以下圖示
// import { Pencil, Trash2 } from 'lucide-react';



export default function Comment({ comments = [] }: CommentProps) {
    
    return (
        <>
            <Head title="首頁動態" />

            <div className="mx-auto max-w-3xl space-y-6">
                {/* 頁面標題區塊 */}
                <div className="rounded-xl border bg-card p-6 shadow-xs">
                    <h1 className="text-2xl font-bold tracking-tight">歡迎來到留言區</h1>
                    <p className="mt-1.5 text-sm text-muted-foreground">
                        在下方分享你的想法，所有動態都會即時顯示在公開留言板中。
                    </p>
                </div>

              <PostCommentTextarea />

                {/* 留言列表區塊（由新到舊） */}
                <div className="space-y-4">
                    {comments.length === 0 ? (
                        <div className="rounded-xl border border-dashed p-10 text-center text-sm text-muted-foreground">
                            目前還沒有任何留言，成為第一個發言的人吧！
                        </div>
                    ) : (
                        comments.map((comment) => {
                            return (
                              <Fragment key={comment.id}>
                                <TheComment comment={comment} />
                              </Fragment>
                            );
                        })
                    )}
                </div>
            </div>
        </>
    );
}

