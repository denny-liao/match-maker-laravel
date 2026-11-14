import React from "react"
import { Head, usePage } from "@inertiajs/react"
import {
    Home,
} from "lucide-react"

import Title from "./AppTitle"
import AppSidebarMobile from "./app/AppSidebarMobile"
import { ThemeToggle } from "@/components/ThemeToggle"
import AuthHeader from "./auth/AuthHeader"
import type { User } from "@/types"
import AppSidebarDesktop from "./app/AppSidebarDesktop"
import { Toaster } from "@/components/ui/toast"
import { FlashToaster } from "@/components/FlashToaster"

const navItems = [
    { title: "首頁動態", href: "/", icon: Home },
    // { title: "探索發現", href: "/explore", icon: Compass },
    // { title: "個人資訊", href: "/settings", icon: UserCircle },
]

interface AppLayoutProps {
    children: React.ReactNode
}

export default function AppLayout({ children }: AppLayoutProps) {
    const { url, props, flash } = usePage<{
        auth?: { user?: User }
    }>()
    // const user = props.auth?.user


    return (
        <>
            <Head>
                <title>My app</title>
                <meta
                    head-key="description"
                    name="description"
                    content="This is the default description"
                />
                <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
            </Head>
            <div className="min-h-screen bg-background text-foreground">
                {/* 1. 頂部固定 Header */}
                <header className="sticky top-0 z-50 flex h-16 w-full items-center justify-between border-b bg-background/95 px-4 backdrop-blur supports-backdrop-filter:bg-background/60">
                    {/* 最左邊：手機版漢堡按鈕 + Logo 與 Title */}
                    <div className="flex items-center gap-3">
                        {/* 手機端側邊攔 */}
                        <AppSidebarMobile navItems={navItems} url={url} />
                        {/* Header 網站名稱 */}
                        <Title />
                    </div>
                    {/* 最右邊：登入 / 註冊 或 會員頭像選單 */}
                    <div className="flex items-center gap-2">
                        {/* 顏色主題 */}
                        <ThemeToggle />
                        {/* TODO: 登入、註冊 */}
                        {/* <AuthHeader user={user} /> */}
                    </div>
                </header>

                {/* 2. 主體區塊：左側 Hover 側邊欄 + 右側主要互動內容區 */}
                <div className="flex">
                    {/* 桌面端側邊攔 */}
                    <AppSidebarDesktop navItems={navItems} url={url} />
                    {/* 主要內容區 */}
                    <main className="flex-1 md:pl-16">
                        <div className="container mx-auto p-4 md:p-6">{children}</div>
                    </main>

                     <Toaster />
                     <FlashToaster flash={flash} />
                </div>
            </div>
        </>
    )
}
