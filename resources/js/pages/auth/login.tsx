import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    demoAccounts: { label: string; email: string }[] | null;
}

export default function Login({ status, canResetPassword, demoAccounts }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title="Zaloguj się" description="Grafik, wymiany zmian i dyspozycyjność w jednym miejscu">
            <Head title="Logowanie" />

            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">Adres e-mail</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="jan@kino.pl"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center">
                            <Label htmlFor="password">Hasło</Label>
                            {canResetPassword && (
                                <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={5}>
                                    Nie pamiętasz hasła?
                                </TextLink>
                            )}
                        </div>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Hasło"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="flex items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            tabIndex={3}
                            checked={data.remember}
                            onCheckedChange={(checked) => setData('remember', checked === true)}
                        />
                        <Label htmlFor="remember">Zapamiętaj mnie</Label>
                    </div>

                    <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Zaloguj
                    </Button>
                </div>

                <p className="text-muted-foreground text-center text-sm">Konto zakłada kierownik kina.</p>
            </form>

            {demoAccounts && (
                <div className="bg-secondary/60 rounded-md border p-3 text-sm">
                    <p className="mb-2 font-medium">Konta demo (hasło: password)</p>
                    <div className="grid gap-2">
                        {demoAccounts.map((account) => (
                            <button
                                key={account.email}
                                type="button"
                                className="bg-background hover:border-primary flex items-center justify-between rounded-md border px-3 py-2 text-left"
                                onClick={() => setData({ ...data, email: account.email, password: 'password' })}
                            >
                                <span className="font-medium">{account.label}</span>
                                <span className="text-muted-foreground text-xs">{account.email}</span>
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}
        </AuthLayout>
    );
}
