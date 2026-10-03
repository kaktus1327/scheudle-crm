import { DocumentStatus } from '@/components/document-status';
import { EmptyState, Panel } from '@/components/panel';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/format';
import { type UserDocument } from '@/types';
import { Link, router, useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { type FormEventHandler, useState } from 'react';

interface Props {
    documents: UserDocument[];
    types: { id: number; name: string; remind_days_before: number; documents_count: number }[];
    filters: { type?: number; within?: number };
}

export default function Documents({ documents, types, filters }: Props) {
    const [creating, setCreating] = useState(false);

    const applyFilter = (patch: Partial<Props['filters']>) =>
        router.get(route('manager.documents.index'), { ...filters, ...patch }, { preserveState: true, preserveScroll: true, replace: true });

    return (
        <AppLayout
            title="Dokumenty pracowników"
            actions={
                <Button variant="outline" onClick={() => setCreating(true)}>
                    <Plus /> Typ dokumentu
                </Button>
            }
        >
            <div className="space-y-3">
                <div className="grid grid-cols-2 gap-2">
                    <NativeSelect
                        className="bg-background"
                        value={filters.type ?? ''}
                        onChange={(e) => applyFilter({ type: e.target.value ? Number(e.target.value) : undefined })}
                    >
                        <option value="">Wszystkie typy</option>
                        {types.map((t) => (
                            <option key={t.id} value={t.id}>
                                {t.name} ({t.documents_count})
                            </option>
                        ))}
                    </NativeSelect>
                    <NativeSelect
                        className="bg-background"
                        value={filters.within ?? ''}
                        onChange={(e) => applyFilter({ within: e.target.value ? Number(e.target.value) : undefined })}
                    >
                        <option value="">Dowolny termin</option>
                        <option value="7">Kończy się w 7 dni</option>
                        <option value="30">Kończy się w 30 dni</option>
                        <option value="60">Kończy się w 60 dni</option>
                        <option value="90">Kończy się w 90 dni</option>
                    </NativeSelect>
                </div>

                <Panel flush description="Posortowane od najszybciej kończących się">
                    {documents.length === 0 ? (
                        <EmptyState>Brak dokumentów dla wybranych filtrów.</EmptyState>
                    ) : (
                        <ul className="divide-y">
                            {documents.map((doc) => (
                                <li key={doc.id}>
                                    <Link
                                        href={doc.user ? route('manager.users.edit', doc.user.id) : '#'}
                                        className="hover:bg-muted/60 flex items-center gap-3 px-4 py-3"
                                    >
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-medium">{doc.user?.name}</p>
                                            <p className="text-muted-foreground text-xs">
                                                {doc.type?.name} · do {formatDate(doc.valid_until, 'd.MM.yyyy')}
                                            </p>
                                        </div>
                                        <DocumentStatus document={doc} />
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </Panel>
            </div>

            <NewTypeDialog open={creating} onClose={() => setCreating(false)} />
        </AppLayout>
    );
}

function NewTypeDialog({ open, onClose }: { open: boolean; onClose: () => void }) {
    const { data, setData, post, processing, errors, reset } = useForm({ name: '', remind_days_before: 30 });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('manager.document-types.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={(o) => !o && onClose()}>
            <DialogContent>
                <form onSubmit={submit} className="grid gap-4">
                    <DialogHeader>
                        <DialogTitle>Nowy typ dokumentu</DialogTitle>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor="type-name">Nazwa</Label>
                        <Input
                            id="type-name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Np. Badania okresowe"
                            required
                        />
                        {errors.name && <p className="text-destructive text-sm">{errors.name}</p>}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="type-days">Przypomnij ile dni wcześniej</Label>
                        <Input
                            id="type-days"
                            type="number"
                            min={1}
                            max={365}
                            value={data.remind_days_before}
                            onChange={(e) => setData('remind_days_before', Number(e.target.value))}
                        />
                    </div>
                    <DialogFooter>
                        <Button type="submit" disabled={processing}>
                            Dodaj
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
