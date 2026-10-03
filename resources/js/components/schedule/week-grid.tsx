import { formatDate } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type Position, type Shift, type WeekDay } from '@/types';
import { type ReactNode } from 'react';

interface WeekGridProps {
    days: WeekDay[];
    positions: Position[];
    shifts: Shift[];
    renderShift: (shift: Shift) => ReactNode;
    renderDayAction?: (day: WeekDay) => ReactNode;

    columnClassName?: string;
}

export function WeekGrid({ days, positions, shifts, renderShift, renderDayAction, columnClassName }: WeekGridProps) {
    const bands = [days.slice(0, 3), days.slice(3)];

    return (
        <div className="space-y-4">
            {bands.map((band, i) => (
                <GridBand
                    key={i}
                    days={band}
                    positions={positions}
                    shifts={shifts}
                    renderShift={renderShift}
                    renderDayAction={renderDayAction}
                    columnClassName={columnClassName}
                    label={i === 0 ? 'Weekend' : 'Tydzień'}
                />
            ))}
        </div>
    );
}

function GridBand({ days, positions, shifts, renderShift, renderDayAction, columnClassName = 'min-w-60', label }: WeekGridProps & { label: string }) {
    const shiftsFor = (date: string, positionId: number) =>
        shifts.filter((s) => s.date === date && s.position?.id === positionId).sort((a, b) => a.start_time.localeCompare(b.start_time));

    const usedPositions = positions.filter((p) => days.some((d) => shiftsFor(d.date, p.id).length > 0));

    return (
        <div className="bg-background overflow-x-auto rounded-md border">
            <table className="w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th className="bg-background text-muted-foreground sticky left-0 z-10 w-24 border-b p-2 text-left text-[11px] font-medium tracking-wide uppercase">
                            {label}
                        </th>
                        {days.map((day) => (
                            <th key={day.date} className={cn('border-b border-l p-2 text-left', columnClassName)}>
                                <div className="flex items-center justify-between gap-2">
                                    <span className={cn('text-xs font-semibold', day.isWeekend && 'text-primary')}>
                                        {formatDate(day.date, 'EEEE d.MM')}
                                    </span>
                                    {renderDayAction?.(day)}
                                </div>
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {usedPositions.length === 0 && (
                        <tr>
                            <td colSpan={days.length + 1} className="text-muted-foreground p-4 text-center text-xs">
                                Brak zmian
                            </td>
                        </tr>
                    )}
                    {usedPositions.map((position) => (
                        <tr key={position.id}>
                            <th className="bg-background text-muted-foreground sticky left-0 z-10 border-b p-2 text-left align-top text-[11px] font-semibold tracking-wide uppercase">
                                {position.name}
                            </th>
                            {days.map((day) => (
                                <td key={day.date} className="border-b border-l p-1.5 align-top">
                                    <div className="grid gap-1">{shiftsFor(day.date, position.id).map(renderShift)}</div>
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
