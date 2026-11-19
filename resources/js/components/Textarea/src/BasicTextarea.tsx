import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { BasicTextareaProps } from './types';

export default function BasicTextarea({
    buttonContent,
    ...params
}: BasicTextareaProps) {
    const { data, onChange, processing, errors } = params;
    return (
        <>
            {/* 頂部新增留言輸入框 */}
            <Textarea
                value={data.message}
                onChange={(e) => onChange('message', e.target.value)}
                placeholder="寫下你想說的話..."
                rows={3}
                className="resize-none border-0 bg-transparent p-1 text-base shadow-none focus-visible:ring-0"
                maxLength={255}
            />

            {errors.message && (
                <p className="mt-1 px-1 text-xs text-destructive">
                    {errors.message}
                </p>
            )}

            {/* 右下角新增按鈕 */}
            <div className="mt-3 flex items-center justify-end space-x-3 border-t pt-3">
                {buttonContent.map((bt) => (
                    <Button
                        key={bt.type}
                        type="submit"
                        disabled={processing || !data.message.trim()}
                        size="sm"
                        {...(bt.attrs || {})}
                    >
                        {bt.text && (processing ? bt.processingText : bt.text)}
                    </Button>
                ))}
            </div>
        </>
    );
}
