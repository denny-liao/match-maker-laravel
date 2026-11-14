import { Link } from "@inertiajs/react"


export default function AppTitle() {
    return (<>
        {/* Brand Logo & Title */}
        <Link href="/" className="flex items-center gap-2.5">
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground font-bold shadow-sm">
                M
            </div>
            <span className="text-lg font-bold tracking-tight">
                MatchMaker
            </span>
        </Link>
    </>)
}