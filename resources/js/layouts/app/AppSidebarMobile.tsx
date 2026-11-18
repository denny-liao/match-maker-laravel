import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from "@/components/ui/sheet"
import { useState } from "react"
import {
    Menu,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Link } from "@inertiajs/react";
import type { NavItem } from "@/types";

export default function AppSidebarMobile({ navItems, url }:{ navItems: NavItem[], url: string}) {
    const [mobileOpen, setMobileOpen] = useState(false)

    return (<>
     {/* 手機版側邊欄觸發器：Base UI 使用 render 屬性整合 Button */}
                    <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                        <SheetTrigger
                            render={
                                <Button variant="ghost" size="icon" className="md:hidden" />
                            }
                        >
                            <Menu className="h-5 w-5" />
                            <span className="sr-only">開啟選單</span>
                        </SheetTrigger>

                        <SheetContent side="left" className="w-64 p-0">
                            <SheetHeader className="border-b p-4 text-left">
                                <SheetTitle className="flex items-center gap-2">
                                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground font-bold">
                                        M
                                    </div>
                                    <span>MatchMaker</span>
                                </SheetTitle>
                            </SheetHeader>
                            <nav className="flex flex-col gap-1 p-3">
                                {navItems.map((item) => {
                                    const Icon = item.icon
                                    const isActive = url === item.href || (item.href !== "/" && url.startsWith(`${item.href}/`))
                                    return (
                                        <Link
                                            key={item.href}
                                            href={item.href}
                                            onClick={() => setMobileOpen(false)}
                                            className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${isActive
                                                    ? "bg-primary text-primary-foreground"
                                                    : "text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                                                }`}
                                        >
                                            <Icon className="h-5 w-5 shrink-0" />
                                            <span>{item.title}</span>
                                        </Link>
                                    )
                                })}
                            </nav>
                        </SheetContent>
                    </Sheet>
    </>)
}