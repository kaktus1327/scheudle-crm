import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

export default function FirstPasswordChange() {
    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('password.first-change.update'), { onError: () => reset('password', 'password_confirmation') });
    };

    return (
        <AuthLayout title="Ustaw własne hasło" description="Zalogowano hasłem jednorazowym. Zanim przejdziesz dalej, ustaw własne hasło.">
            <Head title="Ustaw hasło" />

            <form onSubmit={submit} className="grid gap-5">
                <div className="grid gap-2">
                    <Label htmlFor="current_password">Hasło jednorazowe z maila</Label>
                    <Input
                        id="current_password"
                        type="password"
                        autoComplete="current-password"
                        value={data.current_password}
                        onChange={(e) => setData('current_password', e.target.value)}
                        required
                    />
                    <InputError message={errors.current_password} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="password">Nowe hasło</Label>
                    <Input
                        id="password"
                        type="password"
                        autoComplete="new-password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />
                    <p className="text-muted-foreground text-xs">Min. 8 znaków, litery i cyfry.</p>
                    <InputError message={errors.password} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="password_confirmation">Powtórz nowe hasło</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        autoComplete="new-password"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                    />
                </div>

                <Button type="submit" className="w-full" disabled={processing}>
                    Zapisz i przejdź dalej
                </Button>

                <Link href={route('logout')} method="post" as="button" className="text-muted-foreground text-sm underline">
                    Wyloguj
                </Link>
            </form>
        </AuthLayout>
    );
}
