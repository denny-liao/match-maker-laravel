import { router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { useAuth } from '@/hooks/useAuth';
import { PageEnum } from '@/enums/pageEnum';
import { BasicTextarea, ButtonContent } from '@/components/Textarea';
import { TextareaButtonEnum } from '@/enums/appEnum';

const buttonContent: ButtonContent[] = [
    {
        type: TextareaButtonEnum.SUBMIT,
        processingText: '發佈中...',
        text: '發佈留言',
    },
];

export default function PostCommentTextarea() {
    // 使用 Inertia 的 useForm 管理表單狀態與送出
    const { data, setData, post, processing, reset, errors } = useForm({
        message: '',
    });
    const { isAuthenticated } = useAuth();

    const handleSubmit = (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (!data.message.trim()) return;
        // 未登入
        if (!isAuthenticated) {
            router.visit(PageEnum.BASE_LOGIN);
            return;
        }

        // 請依據您的 Laravel 路由名稱調整 (例如 '/comments' 或 route('comments.store'))
        post('/comments', {
            preserveScroll: true,
            onSuccess: () => reset('message'),
        });
    };
    return (
        <>
            {/* 頂部新增留言輸入框 */}
            <form
                onSubmit={handleSubmit}
                className="rounded-xl border bg-card p-4 shadow-xs transition-colors focus-within:border-ring"
            >
                <BasicTextarea
                    buttonContent={buttonContent}
                    {...{ data, onChange: setData, processing, errors }}
                />
            </form>
        </>
    );
}
