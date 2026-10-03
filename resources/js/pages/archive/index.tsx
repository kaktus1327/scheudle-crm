import { EmptyState, Panel } from '@/components/panel';
import AppLayout from '@/layouts/app-layout';
import { formatRelative } from '@/lib/format';
import { type Schedule } from '@/types';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

export default function Archive({ schedules }: { schedules: Schedule[] }) {
    return (
        <AppLayout title="Archiwum grafików">
            <Panel flush>
                {schedules.length === 0 ? (
                    <EmptyState>Brak opublikowanych grafików.</EmptyState>
                ) : (
                    <ul className="divide-y">
                        {schedules.map((schedule) => (
                            <li key={schedule.id}>
                                <Link href={route('archive.show', schedule.id)} className="hover:bg-muted/60 flex items-center gap-3 px-4 py-3">
                                    <div className="flex-1">
                                        <p className="text-sm font-medium tabular-nums">{schedule.week.label}</p>
                                        <p className="text-muted-foreground text-xs">
                                            {schedule.shifts_count} zmian
                                            {schedule.published_at && ` · opublikowano ${formatRelative(schedule.published_at)}`}
                                        </p>
                                    </div>
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
