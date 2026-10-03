import { EmptyState, Panel } from '@/components/panel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type CinemaWeek, type Schedule } from '@/types';
import { Link, router } from '@inertiajs/react';
import { ChevronRight, Plus } from 'lucide-react';

interface Props {
    schedules: Schedule[];
    nextWeek: CinemaWeek;
    nextWeekExists: boolean;
}

export default function Schedules({ schedules, nextWeek, nextWeekExists }: Props) {
    return (
        <AppLayout
            title="Grafiki"
            actions={
                !nextWeekExists && (
                    <Button onClick={() => router.post(route('manager.schedules.store'), { week_start: nextWeek.start })}>
                        <Plus /> Grafik na {nextWeek.label}
                    </Button>
                )
            }
        >
            <Panel flush>
                {schedules.length === 0 ? (
                    <EmptyState>Nie ma jeszcze żadnego grafiku.</EmptyState>
                ) : (
                    <ul className="divide-y">
                        {schedules.map((schedule) => (
                            <li key={schedule.id}>
                                <Link
                                    href={route('manager.schedules.edit', schedule.id)}
                                    className="hover:bg-muted/60 flex items-center gap-3 px-4 py-3"
                                >
                                    <div className="flex-1">
                                        <p className="text-sm font-medium tabular-nums">{schedule.week.label}</p>
                                        <p className="text-muted-foreground text-xs">{schedule.shifts_count} zmian</p>
                                    </div>
                                    <Badge variant={schedule.status === 'published' ? 'success' : 'open'}>{schedule.status_label}</Badge>
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
