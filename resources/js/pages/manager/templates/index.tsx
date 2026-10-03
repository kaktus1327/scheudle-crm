import { EmptyState, Panel } from '@/components/panel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { ChevronRight, Plus } from 'lucide-react';

interface Template {
    id: number;
    name: string;
    description: string | null;
    is_system: boolean;
    shifts_count: number;
}

export default function Templates({ templates }: { templates: Template[] }) {
    return (
        <AppLayout
            title="Szablony grafiku"
            actions={
                <Button asChild>
                    <Link href={route('manager.templates.create')}>
                        <Plus /> Nowy szablon
                    </Link>
                </Button>
            }
        >
            <Panel flush>
                {templates.length === 0 ? (
                    <EmptyState>Brak szablonów.</EmptyState>
                ) : (
                    <ul className="divide-y">
                        {templates.map((t) => (
                            <li key={t.id}>
                                <Link href={route('manager.templates.edit', t.id)} className="hover:bg-muted/60 flex items-center gap-3 px-4 py-3">
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-medium">{t.name}</p>
                                        <p className="text-muted-foreground truncate text-xs">
                                            {t.shifts_count} zmian{t.description && ` · ${t.description}`}
                                        </p>
                                    </div>
                                    {t.is_system && <Badge variant="secondary">Systemowy</Badge>}
                                    <ChevronRight className="text-muted-foreground size-4" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </Panel>
        </AppLayout>
    );
}
