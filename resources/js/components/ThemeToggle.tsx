import React, { useEffect, useState } from 'react';
import { Moon, Sun } from 'lucide-react';
import { Button } from '@/components/ui/button';

export function ThemeToggle() {
    // 初始化狀態：優先讀取 localStorage，若無設定則預設跟隨系統 (system)
    const [isDark, setIsDark] = useState<boolean>(() => {
        if (typeof window !== 'undefined') {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') return true;
            if (savedTheme === 'light') return false;
            return window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        return false;
    });

    // 同步更新 <html> 的 dark class
    useEffect(() => {
        const root = window.document.documentElement;
        if (isDark) {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }
    }, [isDark]);

    // 監聽系統主題變化：當使用者尚未手動點擊切換過時，自動跟著系統變色
    useEffect(() => {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const handleSystemChange = (e: MediaQueryListEvent) => {
            if (!localStorage.getItem('theme')) {
                setIsDark(e.matches);
            }
        };

        mediaQuery.addEventListener('change', handleSystemChange);
        return () =>
            mediaQuery.removeEventListener('change', handleSystemChange);
    }, []);

    // 點擊直接反轉明暗並儲存偏好
    const toggleTheme = () => {
        const nextIsDark = !isDark;
        setIsDark(nextIsDark);
        localStorage.setItem('theme', nextIsDark ? 'dark' : 'light');
    };

    return (
        <Button
            variant="ghost"
            size="icon"
            onClick={toggleTheme}
            className="relative h-9 w-9"
            aria-label="切換深淺色主題"
            title={isDark ? '切換至淺色模式' : '切換至深色模式'}
            suppressHydrationWarning
        >
            <Sun className="h-4 w-4 scale-100 rotate-0 transition-all dark:scale-0 dark:-rotate-90" />
            <Moon className="absolute h-4 w-4 scale-0 rotate-90 transition-all dark:scale-100 dark:rotate-0" />
        </Button>
    );
}
