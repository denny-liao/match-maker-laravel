import { useEffect, useRef } from 'react';
import { router, usePage } from '@inertiajs/react';
import { toast } from '@/components/ui/toast';

type ToastType = 'success' | 'error' | 'warning' | 'info' | 'loading';

interface FlashToastPayload {
    type?: ToastType;
    title?: string;
    description?: string;
}

interface FlashData {
    toast?: FlashToastPayload;
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

export function FlashToaster({ flash }: {flash: FlashData}) {
    const processedRef = useRef<string | null>(null);
    const triggerToast = (data?: FlashData) => {
        if (!data) return;

        const toastStr = JSON.stringify(data);
        if (toastStr === '{}') return;
        processedRef.current = toastStr;
        // 1. 處理結構化通知: Inertia::flash('toast', ['type' => 'success', 'title' => '...', 'description' => '...'])
        if (data.toast && (data.toast.title || data.toast.description)) {
            toast.add({
                type: data.toast.type ?? 'info',
                title: data.toast.title,
                description: data.toast.description,
            });
            return;
        }

        // 2. 處理簡寫字串: Inertia::flash('success', '操作成功')
        const quickTypes: ToastType[] = ['success', 'error', 'warning', 'info'];
        for (const type of quickTypes) {
            if (Reflect.has(data, type)) {
                toast.add({
                    type,
                    title: Reflect.get(data, type),
                });
                break;
            }
        }
    };

    // 處理首次頁面載入時的 flash 資料
    useEffect(() => {
        triggerToast(flash);
    }, [flash]);

    // 監聽 Inertia 後續非同步導航與表單提交的 flash 事件
    useEffect(() => {
        const removeListener = router.on('flash', (event) => {
            triggerToast(event.detail.flash as FlashData);
        });

        return () => removeListener();
    }, []);

    return null;
}
