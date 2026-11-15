import React, { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

interface ForgotPasswordProps {
    status?: string;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <>
            <Head title="忘記密碼" />

            <div className="flex min-h-screen items-center justify-center bg-muted/40 p-4">
                <Card className="w-full max-w-sm">
                    <CardHeader className="space-y-1">
                        <CardTitle className="text-xl font-semibold tracking-tight">
                            忘記密碼
                        </CardTitle>
                        <CardDescription>
                            請輸入您的電子郵件，我們將寄送密碼重設連結給您。
                        </CardDescription>
                    </CardHeader>

                    <form onSubmit={submit}>
                        <CardContent className="space-y-3">
                            {/* 寄送成功狀態提示 */}
                            {status && (
                                <div className="rounded-md bg-emerald-500/10 px-3 py-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                    {status}
                                </div>
                            )}

                            {/* Email 欄位 */}
                            <div className="*:mb-2.5">
                                <Label htmlFor="email">電子郵件</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="name@example.com"
                                    value={data.email}
                                    autoComplete="username"
                                    autoFocus
                                    onChange={(e) => setData('email', e.target.value)}
                                />
                                {errors.email && (
                                    <p className="text-xs text-destructive">{errors.email}</p>
                                )}
                            </div>
                        </CardContent>

                        <CardFooter className="flex flex-col space-y-3">
                            <Button type="submit" className="w-full" disabled={processing}>
                                {processing ? '寄送中...' : '寄送密碼重設連結'}
                            </Button>

                            <p className="text-center text-xs text-muted-foreground">
                                想起來了？{' '}
                                <Link
                                    href="/login"
                                    className="font-medium text-primary underline-offset-4 hover:underline"
                                >
                                    返回登入
                                </Link>
                            </p>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </>
    );
}
