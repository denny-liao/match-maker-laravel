import { Link } from '@inertiajs/react';
import { Settings, LogOut, User } from 'lucide-react';

import { Button, buttonVariants } from '@/components/ui/button';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
    DropdownMenuGroup,
} from '@/components/ui/dropdown-menu';
import { useAuth } from '@/hooks/useAuth';
import { ProfileAvatar } from '@/features/Profile';

export default function AuthHeader() {
    const { user, isAuthenticated } = useAuth();
    return (
        <>
            {isAuthenticated ? (
                <DropdownMenu>
                    <DropdownMenuTrigger
                        render={
                            <Button
                                variant="ghost"
                                className="relative h-9 w-9 rounded-full"
                            />
                        }
                    >
                        <ProfileAvatar name={user.name} />
                    </DropdownMenuTrigger>

                    <DropdownMenuContent className="w-56" align="end">
                        <DropdownMenuGroup>
                            <DropdownMenuLabel className="font-normal">
                                <div className="flex flex-col space-y-1">
                                    <p className="text-sm leading-none font-medium">
                                        {user.name}
                                    </p>
                                    <p className="text-xs leading-none text-muted-foreground">
                                        {user.email}
                                    </p>
                                </div>
                            </DropdownMenuLabel>
                        </DropdownMenuGroup>
                        <DropdownMenuSeparator />

                        {/* Base UI 的 DropdownMenuItem 使用 render 屬性來渲染 Inertia Link */}
                        {/* <DropdownMenuGroup>
                            <DropdownMenuItem
                                render={<Link href="/profile" className="cursor-pointer" />}

                            >
                                <User className="mr-2 h-4 w-4" />
                                <span>個人主頁</span>
                            </DropdownMenuItem>

                            <DropdownMenuItem
                                render={<Link href="/settings" className="cursor-pointer" />}

                            >
                                <Settings className="mr-2 h-4 w-4" />
                                <span>設定</span>
                            </DropdownMenuItem>
                        </DropdownMenuGroup>
                        <DropdownMenuSeparator /> */}

                        <DropdownMenuGroup>
                            <DropdownMenuItem
                                render={
                                    <Link
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        className="w-full cursor-pointer text-destructive focus:text-destructive"
                                    />
                                }
                                // inertia 屬性 as="button" => 原生按鈕
                                nativeButton={true}
                            >
                                <LogOut className="mr-2 h-4 w-4" />
                                <span>登出</span>
                            </DropdownMenuItem>
                        </DropdownMenuGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            ) : (
                <>
                    {/* Base UI 中直接用 buttonVariants 搭配 Inertia Link 最乾淨且無多餘 DOM 層級 */}
                    <Link
                        href="/login"
                        className={buttonVariants({ variant: 'ghost' })}
                    >
                        登入
                    </Link>
                    <Link href="/register" className={buttonVariants()}>
                        註冊
                    </Link>
                </>
            )}
        </>
    );
}
