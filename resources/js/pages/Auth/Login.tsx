import React, { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface LoginProps {
    status?: string;
    canResetPassword?: boolean;
}

export default function Login({
    status,
    canResetPassword = false,
}: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: 'test1@example.com',
        password: 'test1234',
        remember: false,
    });
    const submit = (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="登入" />

            <div className="flex min-h-screen items-center justify-center bg-muted/40 p-4">
                <Card className="w-full max-w-sm">
                    <CardHeader className="space-y-1">
                        <CardTitle className="text-xl font-semibold tracking-tight">
                            登入帳號
                        </CardTitle>
                        <CardDescription>
                            請輸入您的電子郵件與密碼以繼續
                        </CardDescription>
                    </CardHeader>

                    <form onSubmit={submit}>
                        <CardContent className="space-y-3">
                            {/* Fortify 狀態訊息 (如密碼重設成功) */}
                            {status && (
                                <div className="rounded-md bg-emerald-500/10 px-3 py-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                    {status}
                                </div>
                            )}

                            {/* Email 欄位 */}
                            <div className="space-y-1.5">
                                <Label htmlFor="email">電子郵件</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="name@example.com"
                                    value={data.email}
                                    autoComplete="username"
                                    autoFocus
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                />
                                {errors.email && (
                                    <p className="text-xs text-destructive">
                                        {errors.email}
                                    </p>
                                )}
                            </div>

                            {/* Password 欄位 */}
                            <div className="space-y-1.5">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password">密碼</Label>
                                    {/* {canResetPassword && (
                                        <Link
                                            href="/forgot-password"
                                            className="text-xs text-muted-foreground underline-offset-4 hover:text-primary hover:underline"
                                        >
                                            忘記密碼？
                                        </Link>
                                    )} */}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    autoComplete="current-password"
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                />
                                {errors.password && (
                                    <p className="text-xs text-destructive">
                                        {errors.password}
                                    </p>
                                )}
                            </div>

                            {/* 記住我 (Base UI Checkbox 寫法) */}
                            <div className="flex items-center space-x-2 pt-1 pb-2">
                                <Checkbox
                                    id="remember"
                                    checked={data.remember}
                                    onCheckedChange={(checked) =>
                                        setData('remember', Boolean(checked))
                                    }
                                />
                                <Label
                                    htmlFor="remember"
                                    className="cursor-pointer text-xs font-normal text-muted-foreground"
                                >
                                    保持登入狀態
                                </Label>
                            </div>
                        </CardContent>

                        <CardFooter className="flex flex-col space-y-3">
                            <Button
                                type="submit"
                                className="w-full"
                                disabled={processing}
                            >
                                {processing ? '登入中...' : '登入'}
                            </Button>

                            <p className="text-center text-xs text-muted-foreground">
                                還沒有帳號？{' '}
                                <Link
                                    href="/register"
                                    className="font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    立即註冊
                                </Link>
                            </p>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </>
    );
}
