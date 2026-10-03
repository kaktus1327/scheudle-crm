import { EmptyState } from '@/components/panel';
import { formatDate } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type CinemaWeek, type Position, type Shift } from '@/types';
import { ShiftChip } from './shift-chip';
import { ScheduleViewToggle, useScheduleView } from './view-toggle';
import { WeekGrid } from './week-grid';

interface WeekScheduleProps {
    week: CinemaWeek;
    shifts: Shift[];
    positions: Position[];
    variant?: 'current' | 'original';
    highlightUserId?: number;
}

export function WeekSchedule({ week, shifts, positions, variant = 'current', highlightUserId }: WeekScheduleProps) {
    const [view, setView] = useScheduleView();

    if (shifts.length === 0) {
        return <EmptyState>Brak zmian w tym tygodniu.</EmptyState>;
    }

    const chip = (shift: Shift) => <ShiftChip key={shift.id} shift={shift} variant={variant} highlightUserId={highlightUserId} />;

    return (
        <div className="space-y-3">
            <ScheduleViewToggle view={view} onChange={setView} />

            {view === 'list' && (
                <div className="space-y-3 lg:hidden">
                    {week.days.map((day) => {
                        const dayShifts = shifts.filter((s) => s.date === day.date);

                        return (
                            <section key={day.date} className="bg-background rounded-md border">
                                <h3 className={cn('border-b px-3 py-2 text-sm font-semibold', day.isWeekend && 'text-primary')}>
                                    {formatDate(day.date)}
                                </h3>
                                <div className="space-y-3 p-3">
                                    {dayShifts.length === 0 && <p className="text-muted-foreground text-xs">Brak zmian</p>}
                                    {positions.map((position) => {
                                        const list = dayShifts
                                            .filter((s) => s.position?.id === position.id)
                                            .sort((a, b) => a.start_time.localeCompare(b.start_time));
                                        if (list.length === 0) return null;

                                        return (
                                            <div key={position.id}>
                                                <p className="text-muted-foreground mb-1 text-[11px] font-medium tracking-wide uppercase">
                                                    {position.name}
                                                </p>
                                                <div className="grid gap-1">{list.map(chip)}</div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </section>
                        );
                    })}
                </div>
            )}

            <div className={cn(view === 'list' && 'hidden lg:block')}>
                <WeekGrid days={week.days} positions={positions} shifts={shifts} renderShift={chip} />
            </div>
        </div>
    );
}

export function ScheduleLegend() {
    return (
        <div className="text-muted-foreground flex flex-wrap items-center gap-3 text-xs">
            <span className="flex items-center gap-1.5">
                <span className="border-primary bg-mine size-3 rounded-sm border" /> Twoje zmiany
            </span>
            <span className="flex items-center gap-1.5">
                <span className="bg-open size-3 rounded-sm" /> Wolna zmiana
            </span>
        </div>
    );
}
