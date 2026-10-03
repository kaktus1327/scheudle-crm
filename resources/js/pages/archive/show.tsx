import { ScheduleLegend, WeekSchedule } from '@/components/schedule/week-schedule';
import { Button } from '@/components/ui/button';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import AppLayout from '@/layouts/app-layout';
import { type Position, type Schedule, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useMemo, useState } from 'react';

export default function ArchiveShow({ schedule }: { schedule: Schedule }) {
    const { auth } = usePage<SharedData>().props;
    const [variant, setVariant] = useState<'current' | 'original'>('current');

    const positions = useMemo(() => {
        const map = new Map<number, Position>();
        schedule.shifts?.forEach((s) => s.position && map.set(s.position.id, s.position));
        return [...map.values()];
    }, [schedule.shifts]);

    return (
        <AppLayout
            title={`Grafik ${schedule.week.label}`}
            actions={
                <Button variant="outline" size="sm" asChild>
                    <Link href={route('archive.index')}>
                        <ArrowLeft /> Archiwum
                    </Link>
                </Button>
            }
        >
            <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <ScheduleLegend />
                    <ToggleGroup
                        type="single"
                        value={variant}
                        onValueChange={(v) => v && setVariant(v as 'current' | 'original')}
                        className="bg-background rounded-md border p-0.5"
                    >
                        <ToggleGroupItem value="current" className="h-8 rounded-sm px-3 text-xs">
                            Po wymianach
                        </ToggleGroupItem>
                        <ToggleGroupItem value="original" className="h-8 rounded-sm px-3 text-xs">
                            Oryginalny
                        </ToggleGroupItem>
                    </ToggleGroup>
                </div>
                <WeekSchedule
                    week={schedule.week}
                    shifts={schedule.shifts ?? []}
                    positions={positions}
                    variant={variant}
                    highlightUserId={auth.user.role === 'employee' ? auth.user.id : undefined}
                />
            </div>
        </AppLayout>
    );
}
