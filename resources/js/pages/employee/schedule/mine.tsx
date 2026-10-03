import { EmptyState, Panel } from '@/components/panel';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatHours } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type Shift } from '@/types';

export default function MyShifts({ shifts, today }: { shifts: Shift[]; today: string }) {
    const total = shifts.reduce((sum, s) => sum + s.hours, 0);

    return (
        <AppLayout title="Moje zmiany">
            <Panel title="Od bieżącego tygodnia" description={`Razem ${formatHours(total)}`} flush>
                {shifts.length === 0 ? (
                    <EmptyState>Nie masz jeszcze żadnych zmian.</EmptyState>
                ) : (
                    <ul className="divide-y">
                        {shifts.map((shift) => (
                            <li key={shift.id} className={cn('flex items-center gap-4 px-4 py-3', shift.date < today && 'opacity-50')}>
                                <div className="bg-secondary text-secondary-foreground flex size-12 shrink-0 flex-col items-center justify-center rounded-md">
                                    <span className="text-[10px] uppercase">{formatDate(shift.date, 'EEE')}</span>
                                    <span className="text-lg leading-none font-semibold">{formatDate(shift.date, 'd')}</span>
                                </div>
                                <div className="flex-1">
                                    <p className="text-sm font-medium tabular-nums">
                                        {shift.start_time} - {shift.end_time}
                                    </p>
                                    <p className="text-muted-foreground text-xs">
                                        {shift.position?.name} · {formatHours(shift.hours)}
                                    </p>
                                </div>
                                {shift.date === today && <Badge>Dziś</Badge>}
                                {shift.was_traded && <Badge variant="secondary">Po wymianie</Badge>}
                            </li>
                        ))}
                    </ul>
                )}
            </Panel>
        </AppLayout>
    );
}
