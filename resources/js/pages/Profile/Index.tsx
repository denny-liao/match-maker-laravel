import { ProfileAvatar } from "@/features/Profile";
import { useAuth } from "@/hooks/useAuth";
import { Head } from "@inertiajs/react";


export default function Profile() {
    const { user } = useAuth()
    return (<>
        <Head title="個人檔案" />
        <div className="mx-auto max-w-5xl space-y-8">
            <section className="space-y-4">
                <div className="flex flex-col items-center gap-3 py-12 text-center">
                    <ProfileAvatar name={user.name} className="w-20 h-20 md:w-24 md:h-24" />
                    <h2 className="text-xl font-semibold">{user.name}</h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {user.email}
                    </p>
                </div>
            </section>
        </div>
    </>)
}