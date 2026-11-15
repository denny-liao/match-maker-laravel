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

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/register', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="註冊" />

            <div className="flex min-h-screen items-center justify-center bg-muted/40 p-4">
                <Card className="w-full max-w-sm">
                    <CardHeader className="space-y-1">
                        <CardTitle className="text-xl font-semibold tracking-tight">
                            建立新帳號
                        </CardTitle>
                        <CardDescription>
                            填寫以下資訊以完成註冊
                        </CardDescription>
                    </CardHeader>

                    <form onSubmit={submit}>
                        <CardContent className="space-y-3 pb-4">
                            {/* Name 欄位 */}
                            <div className="space-y-1.5">
                                <Label htmlFor="name">名稱</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    placeholder="王小明"
                                    value={data.name}
                                    autoComplete="name"
                                    autoFocus
                                    onChange={(e) => setData('name', e.target.value)}
                                />
                                {errors.name && (
                                    <p className="text-xs text-destructive">{errors.name}</p>
                                )}
                            </div>

                            {/* Email 欄位 */}
                            <div className="space-y-1.5">
                                <Label htmlFor="email">電子郵件</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="name@example.com"
                                    value={data.email}
                                    autoComplete="username"
                                    onChange={(e) => setData('email', e.target.value)}
                                />
                                {errors.email && (
                                    <p className="text-xs text-destructive">{errors.email}</p>
                                )}
                            </div>

                            {/* Password 欄位 */}
                            <div className="space-y-1.5">
                                <Label htmlFor="password">密碼</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    autoComplete="new-password"
                                    onChange={(e) => setData('password', e.target.value)}
                                />
                                {errors.password && (
                                    <p className="text-xs text-destructive">{errors.password}</p>
                                )}
                            </div>

                            {/* Password Confirmation 欄位 */}
                            <div className="space-y-1.5">
                                <Label htmlFor="password_confirmation">確認密碼</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    value={data.password_confirmation}
                                    autoComplete="new-password"
                                    onChange={(e) =>
                                        setData('password_confirmation', e.target.value)
                                    }
                                />
                                {errors.password_confirmation && (
                                    <p className="text-xs text-destructive">
                                        {errors.password_confirmation}
                                    </p>
                                )}
                            </div>
                        </CardContent>

                        <CardFooter className="flex flex-col space-y-3">
                            <Button type="submit" className="w-full" disabled={processing}>
                                {processing ? '註冊中...' : '註冊'}
                            </Button>

                            <p className="text-center text-xs text-muted-foreground">
                                已經有帳號了？{' '}
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
