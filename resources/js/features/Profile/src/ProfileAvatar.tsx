import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getRandomAvatar } from "@/lib/utils";

type AvatarProps = React.ComponentProps<typeof Avatar>;
type ProfileAvatarProps = {
    name: string;
} & {
    [K in keyof AvatarProps]: AvatarProps[K];
}

export default function ProfileAvatar({ name, ...props }: ProfileAvatarProps) {

    return (
        <Avatar className='h-9 w-9' {...props}>
            <AvatarImage
                src={getRandomAvatar(name)}
                alt={name}
            />
            <AvatarFallback>
                {name.trim().charAt(0).toUpperCase()}
            </AvatarFallback>
        </Avatar>
    )
}