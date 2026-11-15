import { getRandomAvatar } from "@/lib/utils";
import { User } from "@/types";
import { usePage } from "@inertiajs/react";

export function useAuth() {
    const { auth } = usePage<{ auth?: { user?: User } }>().props;
    const user = auth?.user ?? null;

    return {
        user: {
            id: null,
            name: "",
            email: "",
            avatar: getRandomAvatar(user?.name ?? ""),
            ...(user ?? {}),
        },
        isAuthenticated: Boolean(user),
    };
}
