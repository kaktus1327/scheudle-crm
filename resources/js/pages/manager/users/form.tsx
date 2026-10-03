import { DocumentStatus } from '@/components/document-status';
import InputError from '@/components/input-error';
import { Panel } from '@/components/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type Employee, type Position, type Role, type SharedData } from '@/types';
import { router, useForm, usePage } from '@inertiajs/react';
import { KeyRound, Trash2, UserMinus } from 'lucide-react';
import { type FormEventHandler } from 'react';

interface Props {
    user: Employee | null;
    positions: Position[];
    documentTypes: { id: number; name: string }[];
    roles: { value: Role; label: string }[];
}

export default function UserForm({ user, positions, documentTypes, roles }: Props) {
    const { auth } = usePage<SharedData>().props;
    const isSelf = user?.id === auth.user.id;

    const { data, setData, post, put, processing, errors } = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        phone: user?.phone ?? '',
        role: user?.role ?? ('employee' as Role),
        hourly_rate: user?.hourly_rate ?? 30.5,
        position_ids: user?.positions?.map((p) => p.id) ?? ([] as number[]),
    });

    const togglePosition = (id: number) =>
        setData('position_ids', data.position_ids.includes(id) ? data.position_ids.filter((p) => p !== id) : [...data.position_ids, id]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (user) put(route('manager.users.update', user.id), { preserveScroll: true });
        else post(route('manager.users.store'));
    };

    return (
        <AppLayout title={user ? user.name : 'Nowy pracownik'}>
            <div className="grid gap-4 lg:grid-cols-2">
                <form onSubmit={submit} className="space-y-4">
                    <Panel title="Dane">
                        <div className="grid gap-4">
                            <Field label="Imię i nazwisko" error={errors.name}>
                                <Input value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                            </Field>
                            <Field label="E-mail" error={errors.email} hint={!user ? 'Na ten adres wyślemy hasło jednorazowe.' : undefined}>
                                <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required />
                            </Field>
                            <div className="grid grid-cols-2 gap-3">
                                <Field label="Telefon" error={errors.phone}>
                                    <Input type="tel" value={data.phone} onChange={(e) => setData('phone', e.target.value)} />
                                </Field>
                                <Field label="Stawka (zł/h)" error={errors.hourly_rate}>
                                    <Input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={data.hourly_rate}
                                        onChange={(e) => setData('hourly_rate', Number(e.target.value))}
                                        required
                                    />
                                </Field>
                            </div>
                            <Field label="Rola" error={errors.role}>
                                <NativeSelect value={data.role} disabled={isSelf} onChange={(e) => setData('role', e.target.value as Role)}>
                                    {roles.map((r) => (
                                        <option key={r.value} value={r.value}>
                                            {r.label}
                                        </option>
                                    ))}
                                </NativeSelect>
                            </Field>
                        </div>
                    </Panel>

                    <Panel title="Stanowiska" description="Na jakich stanowiskach ta osoba może pracować">
                        <div className="grid grid-cols-3 gap-2">
                            {positions.map((p) => (
                                <button
                                    key={p.id}
                                    type="button"
                                    onClick={() => togglePosition(p.id)}
                                    className={cn(
                                        'h-10 rounded-md border text-sm font-medium',
                                        data.position_ids.includes(p.id) ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted',
                                    )}
                                >
                                    {p.name}
                                </button>
                            ))}
                        </div>
                    </Panel>

                    <Button type="submit" className="w-full" disabled={processing}>
                        {user ? 'Zapisz zmiany' : 'Utwórz konto i wyślij hasło'}
                    </Button>
                </form>

                {user && (
                    <div className="space-y-4">
                        <DocumentsPanel user={user} documentTypes={documentTypes} />

                        {!isSelf && (
                            <Panel title="Konto">
                                <div className="flex flex-wrap gap-2">
                                    <Button
                                        variant="outline"
                                        onClick={() =>
                                            window.confirm(`Wysłać ${user.name} nowe hasło jednorazowe?`) &&
                                            router.post(route('manager.users.reset-password', user.id), {}, { preserveScroll: true })
                                        }
                                    >
                                        <KeyRound /> Resetuj hasło
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        onClick={() =>
                                            window.confirm(`Zwolnić ${user.name}? Konto zostanie zarchiwizowane, a przyszłe zmiany zwolnione.`) &&
                                            router.delete(route('manager.users.destroy', user.id))
                                        }
                                    >
                                        <UserMinus /> Zwolnij
                                    </Button>
                                </div>
                            </Panel>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function Field({ label, error, hint, children }: { label: string; error?: string; hint?: string; children: React.ReactNode }) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            {children}
            {hint && <p className="text-muted-foreground text-xs">{hint}</p>}
            <InputError message={error} />
        </div>
    );
}

function DocumentsPanel({ user, documentTypes }: { user: Employee; documentTypes: Props['documentTypes'] }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        document_type_id: String(documentTypes[0]?.id ?? ''),
        valid_until: '',
        note: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('manager.documents.store', user.id), { preserveScroll: true, onSuccess: () => reset('valid_until', 'note') });
    };

    return (
        <Panel title="Dokumenty" description="Pracownik dostanie przypomnienie przed końcem ważności" flush>
            <ul className="divide-y">
                {user.documents?.map((doc) => (
                    <li key={doc.id} className="flex items-center gap-3 px-4 py-2.5">
                        <div className="flex-1">
                            <p className="text-sm font-medium">{doc.type?.name}</p>
                            <p className="text-muted-foreground text-xs">do {formatDate(doc.valid_until, 'd.MM.yyyy')}</p>
                        </div>
                        <DocumentStatus document={doc} />
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            aria-label="Usuń dokument"
                            onClick={() =>
                                window.confirm('Usunąć dokument?') &&
                                router.delete(route('manager.documents.destroy', doc.id), { preserveScroll: true })
                            }
                        >
                            <Trash2 />
                        </Button>
                    </li>
                ))}
            </ul>
            {documentTypes.length > 0 && (
                <form onSubmit={submit} className="grid grid-cols-[1fr_1fr_auto] items-end gap-2 border-t p-3">
                    <NativeSelect value={data.document_type_id} onChange={(e) => setData('document_type_id', e.target.value)} className="h-9">
                        {documentTypes.map((t) => (
                            <option key={t.id} value={t.id}>
                                {t.name}
                            </option>
                        ))}
                    </NativeSelect>
                    <Input type="date" value={data.valid_until} onChange={(e) => setData('valid_until', e.target.value)} className="h-9" required />
                    <Button type="submit" size="sm" disabled={processing}>
                        Dodaj
                    </Button>
                    <InputError message={errors.valid_until} className="col-span-3" />
                </form>
            )}
        </Panel>
    );
}
