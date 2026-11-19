import type { ClassValue } from 'clsx';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function getRandomAvatar(str: string) {
    // 組合名稱與 ID 作為隨機種子，產生專屬的 Laravel 匿名頭像
    const avatarSeed = encodeURIComponent(str);
    return `https://avatars.laravel.cloud/${avatarSeed}`;
}
