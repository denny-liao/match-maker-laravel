import { Link, router, useForm } from '@inertiajs/react';
import { useAuth } from '@/hooks/useAuth';
import { PageEnum } from '@/enums/pageEnum';
import { BasicTextarea, ButtonContent } from '@/components/Textarea';
import { TextareaButtonEnum } from '@/enums/appEnum';
import { EditCommentProps } from './types';

const buttonContent: ButtonContent[] = [
    {
        type: TextareaButtonEnum.CANCEL,
        attrs: {
            render: () => <Link href="/" > 取消</Link>,
            variant: "outline"
        }
    },
    {
        type: TextareaButtonEnum.SUBMIT,
        processingText: '儲存中...',
        text: '修改'
    }
]

export default function EditCommentTextarea({ comment } :EditCommentProps) {
    // 使用 Inertia 的 useForm 管理表單狀態與送出
    const { data, setData, put, processing, reset, errors } = useForm({
        message: comment.message,
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
        put(`/comments/${comment.id}`);
    };
    return (<>
        {/* 頂部新增留言輸入框 */}
        <form
            onSubmit={handleSubmit}
            className="rounded-xl border bg-card p-4 shadow-xs transition-colors focus-within:border-ring"
        >
            <BasicTextarea
                buttonContent={buttonContent}
                {...{ data, onChange: setData, processing, errors }} />

        </form>
    </>)

}