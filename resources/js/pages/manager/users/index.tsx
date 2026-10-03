import { EmptyState, Panel } from '@/components/panel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type Employee } from '@/types';
import { Link, router } from '@inertiajs/react';
import { ChevronRight, RotateCcw, TriangleAlert, UserPlus } from 'lucide-react';
import { useState } from 'react';

export default function Users({ users, archived }: { users: Employee[]; archived: boolean }) {
    const [query, setQuery] = useState('');
    const filtered = users.filter((u) => u.name.toLowerCase().includes(query.toLowerCase()));

    return (
        <AppLayout
            title="Pracownicy"
            actions={
                <Button asChild>
                    <Link href={route('manager.users.create')}>
                        <UserPlus /> Dodaj
                    </Link>
                </Button>
            }
        >
            <div className="space-y-3">
                <div className="flex gap-2">
                    <Input placeholder="Szukaj..." value={query} onChange={(e) => setQuery(e.target.value)} className="bg-background" />
                    <div className="bg-background flex shrink-0 rounded-md border p-0.5">
                        {[
                            { label: 'Aktywni', value: false },
                            { label: 'Zwolnieni', value: true },
                        ].map((tab) => (
                            <Link
                                key={tab.label}
                                href={route('manager.users.index', tab.value ? { archived: 1 } : {})}
                                className={cn(
                                    'flex h-8 items-center rounded-sm px-3 text-xs font-medium',
                                    archived === tab.value ? 'bg-primary text-primary-foreground' : 'text-muted-foreground',
                                )}
                            >
                                {tab.label}
                            </Link>
                        ))}
                    </div>
                </div>

                <Panel flush>
                    {filtered.length === 0 ? (
                        <EmptyState>Brak osób.</EmptyState>
                    ) : (
                        <ul className="divide-y">
                            {filtered.map((user) => {
                                const expiring = user.documents?.filter((d) => d.expiring_soon) ?? [];

                                return (
                                    <li key={user.id} className="flex items-center gap-3 px-4 py-3">
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center gap-2">
                                                <p className="truncate text-sm font-medium">{user.name}</p>
                                                {user.role === 'manager' && <Badge>Kierownik</Badge>}
                                                {expiring.length > 0 && (
                                                    <TriangleAlert className="text-warning size-4" aria-label="Kończące się dokumenty" />
                                                )}
                                            </div>
                                            <p className="text-muted-foreground truncate text-xs">{user.email}</p>
                                            <div className="mt-1 flex flex-wrap gap-1">
                                                {user.positions?.map((p) => (
                                                    <Badge key={p.id} variant="secondary">
                                                        {p.name}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </div>
                                        {archived ? (
                                            <Button size="sm" variant="outline" onClick={() => router.post(route('manager.users.restore', user.id))}>
                                                <RotateCcw /> Przywróć
                                            </Button>
                                        ) : (
                                            <Button variant="ghost" size="icon" asChild>
                                                <Link href={route('manager.users.edit', user.id)} aria-label="Edytuj">
                                                    <ChevronRight />
                                                </Link>
                                            </Button>
                                        )}
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </Panel>
            </div>
        </AppLayout>
    );
}
