import { Link } from "@inertiajs/react"
import { useState } from "react"
import type { NavItem } from "@/types";



export default function AppSidebarDesktop({ navItems, url }: { navItems: NavItem[], url: String }) {
    const [isHovered, setIsHovered] = useState(false)

    return (
        <>
            {/* 桌面版左側邊欄：預設寬度 w-16 (64px)，Hover 時平滑展開為 w-56 (224px) */}
            <aside
                onMouseEnter={() => setIsHovered(true)}
                onMouseLeave={() => setIsHovered(false)}
                className={`fixed left-0 top-16 z-40 hidden h-[calc(100vh-4rem)] flex-col border-r bg-background transition-all duration-300 ease-in-out md:flex ${isHovered ? "w-56 shadow-lg" : "w-16"
                    }`}
            >
                <nav className="flex flex-col gap-1.5 p-2.5">
                    {navItems.map((item) => {
                        const Icon = item.icon
                        const isActive = url === item.href
                        
                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={`group flex h-11 items-center rounded-lg px-2.5 text-sm font-medium transition-colors ${isActive
                                    ? "bg-primary text-primary-foreground"
                                    : "text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                                    }`}
                            >
                                <div className="flex w-6 shrink-0 items-center justify-center">
                                    <Icon className="h-5 w-5" />
                                </div>
                                <span
                                    className={`ml-3 whitespace-nowrap transition-opacity duration-200 ${isHovered
                                        ? "opacity-100"
                                        : "w-0 overflow-hidden opacity-0"
                                        }`}
                                >
                                    {item.title}
                                </span>
                            </Link>
                        )
                    })}
                </nav>
            </aside>
        </>)

}