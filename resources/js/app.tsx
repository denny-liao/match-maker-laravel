import { createInertiaApp, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { configureEcho } from '@laravel/echo-react';

function getMetaContent(name: string): string | undefined {
    if (typeof document === 'undefined') {
        return undefined;
    }

    return (
        document.querySelector<HTMLMetaElement>(`meta[name="${name}"]`)
            ?.content || undefined
    );
}

const reverbScheme =
    getMetaContent('reverb-scheme') ||
    import.meta.env.VITE_REVERB_SCHEME ||
    'https';

configureEcho({
    broadcaster: 'reverb',
    key: getMetaContent('reverb-key') || import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: getMetaContent('reverb-host') || import.meta.env.VITE_REVERB_HOST,
    wsPort: Number(
        getMetaContent('reverb-port') ||
            import.meta.env.VITE_REVERB_PORT ||
            8080,
    ),
    wssPort: Number(
        getMetaContent('reverb-port') ||
            import.meta.env.VITE_REVERB_PORT ||
            8080,
    ),
    forceTLS: reverbScheme === 'https',
    enabledTransports: ['ws', 'wss'],
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// 處理使用者點擊上一頁、下一頁為 Romm 時返回大廳
router.on('navigate', (event) => {
    if (
        event.detail.type === 'history' &&
        event.detail.page.component === 'Matchmaking/Room'
    ) {
        router.visit('/matchmaking', { replace: true });
    }
});

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#4B5563',
    },
    layout: (name) => (name === 'Matchmaking/Room' ? undefined : AppLayout),
});
