import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="首頁動態" />
            <div className="rounded-xl border bg-card p-6 shadow-sm">
                <h1 className="text-2xl font-bold">歡迎來到留言區</h1>
                <p className="mt-2 text-muted-foreground">
                    將滑鼠移到左側圖示列，側邊欄會自動平滑展開；點擊連結可透過
                    Inertia 無刷新切換頁面。
                </p>
            </div>
        </>
    );
}

// Inertia 持久化佈局寫法：切換頁面時保持 Header 與 Sidebar 狀態不重置
// Home.layout = (page: React.ReactNode) => <AppLayout>{page}</AppLayout>
