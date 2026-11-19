import { Head } from '@inertiajs/react';
import { EditCommentProps, EditCommentTextarea } from '@/features/Comments';

export default function Edit({ comment }: EditCommentProps) {
    return (
        <>
            <Head title="編輯留言" />
            <div className="mx-auto max-w-3xl space-y-6">
                <EditCommentTextarea comment={comment} />
            </div>
        </>
    );
}
