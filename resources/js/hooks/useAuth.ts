import { getRandomAvatar } from "@/lib/utils";
import { User } from "@/types";
import { usePage } from "@inertiajs/react";

export function useAuth() {
    const { auth } = usePage<{ auth?: { user?: User } }>().props;
    const user = auth?.user ?? { id: null, name: "", email: "" };

    return {
        user: {
            ...user,
            avatar: getRandomAvatar(user?.name ?? ""),
        },
        isAuthenticated: Boolean(user),
    };
}
