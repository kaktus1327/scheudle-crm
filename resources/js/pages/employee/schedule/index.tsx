import { EmptyState, Panel } from '@/components/panel';
import { ScheduleLegend, WeekSchedule } from '@/components/schedule/week-schedule';
import { WeekSwitcher } from '@/components/schedule/week-switcher';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import AppLayout from '@/layouts/app-layout';
import { type CinemaWeek, type Position, type Schedule, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    week: CinemaWeek;
    prevWeek: string;
    nextWeek: string;
    schedule: Schedule | null;
    positions: Position[];
}

export default function EmployeeSchedule({ week, prevWeek, nextWeek, schedule, positions }: Props) {
    const { auth } = usePage<SharedData>().props;
    const [variant, setVariant] = useState<'current' | 'original'>('current');

    return (
        <AppLayout title="Grafik">
            <div className="space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <WeekSwitcher week={week} prev={prevWeek} next={nextWeek} routeName="schedule.index" />

                    <ToggleGroup
                        type="single"
                        value={variant}
                        onValueChange={(v) => v && setVariant(v as 'current' | 'original')}
                        className="bg-background rounded-md border p-0.5"
                    >
                        <ToggleGroupItem value="current" className="h-8 rounded-sm px-3 text-xs">
                            Aktualny
                        </ToggleGroupItem>
                        <ToggleGroupItem value="original" className="h-8 rounded-sm px-3 text-xs">
                            Oryginalny
                        </ToggleGroupItem>
                    </ToggleGroup>
                </div>

                <ScheduleLegend />

                {schedule ? (
                    <WeekSchedule week={week} shifts={schedule.shifts ?? []} positions={positions} variant={variant} highlightUserId={auth.user.id} />
                ) : (
                    <Panel>
                        <EmptyState>Grafik na ten tydzień nie został jeszcze opublikowany.</EmptyState>
                    </Panel>
                )}
            </div>
        </AppLayout>
    );
}
