import { router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { useAuth } from '@/hooks/useAuth';
import { PageEnum } from '@/enums/pageEnum';


export default function CommentTextarea() {
    // 使用 Inertia 的 useForm 管理表單狀態與送出
    const { data, setData, post, processing, reset, errors } = useForm({
        message: '',
    });
    const { isAuthenticated } = useAuth()

    const handleSubmit = (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (!data.message.trim()) return;
        // 未登入
        if (!isAuthenticated) {
            router.visit(PageEnum.BASE_LOGIN)
            return
        }

        // 請依據您的 Laravel 路由名稱調整 (例如 '/comments' 或 route('comments.store'))
        post('/comments', {
            preserveScroll: true,
            onSuccess: () => reset('message')
        });
    };
    return (<>
        {/* 頂部新增留言輸入框 */}
        <form
            onSubmit={handleSubmit}
            className="rounded-xl border bg-card p-4 shadow-xs transition-colors focus-within:border-ring"
        >
            <Textarea
                value={data.message}
                onChange={(e) => setData('message', e.target.value)}
                placeholder="寫下你想說的話..."
                rows={3}
                className="resize-none border-0 bg-transparent p-1 text-base shadow-none focus-visible:ring-0"
                maxLength={255}
            />

            {errors.message && (
                <p className="mt-1 px-1 text-xs text-destructive">{errors.message}</p>
            )}

            {/* 右下角新增按鈕 */}
            <div className="mt-3 flex items-center justify-end border-t pt-3">
                <Button
                    type="submit"
                    disabled={processing || !data.message.trim()}
                    size="sm"
                >
                    {processing ? '發佈中...' : '發佈留言'}
                </Button>
            </div>
        </form>
    </>)

}