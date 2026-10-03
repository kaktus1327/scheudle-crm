import { Badge } from '@/components/ui/badge';
import { formatShortDate } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type Shift, type ShiftTrade } from '@/types';
import { ArrowRight, TriangleAlert } from 'lucide-react';
import { type ReactNode } from 'react';

export function ShiftSummary({ shift, muted = false }: { shift?: Shift | null; muted?: boolean }) {
    if (!shift) return null;

    return (
        <div className={cn('rounded-sm border px-2 py-1.5 text-xs', muted ? 'bg-muted' : 'bg-background')}>
            <p className="font-semibold">{formatShortDate(shift.date)}</p>
            <p className="tabular-nums">
                {shift.start_time}-{shift.end_time} · {shift.position?.name}
            </p>
        </div>
    );
}

export function TradeCard({ trade, actions }: { trade: ShiftTrade; actions?: ReactNode }) {
    const receiver = trade.target_user ?? trade.claimer;

    return (
        <article className={cn('bg-background space-y-3 rounded-md border p-3', trade.availability_conflict && 'border-warning bg-warning/5')}>
            <header className="flex flex-wrap items-center justify-between gap-2">
                <div className="flex items-center gap-2">
                    <Badge variant="secondary">{trade.type_label}</Badge>
                    <span className="text-muted-foreground text-xs">{trade.status_label}</span>
                </div>
            </header>

            <div className="flex items-center gap-2 text-sm">
                <span className="font-medium">{trade.requester?.name ?? '-'}</span>
                <ArrowRight className="text-muted-foreground size-4" />
                <span className="font-medium">{receiver?.name ?? 'Ktokolwiek'}</span>
            </div>

            <div className="grid grid-cols-2 gap-2">
                <ShiftSummary shift={trade.offered_shift} />
                {trade.requested_shift && <ShiftSummary shift={trade.requested_shift} muted />}
            </div>

            {trade.note && <p className="text-muted-foreground text-xs italic">"{trade.note}"</p>}

            {trade.availability_conflict && (
                <p className="text-open-foreground flex items-start gap-2 text-xs font-medium">
                    <TriangleAlert className="text-warning mt-0.5 size-4 shrink-0" />
                    {receiver?.name ?? 'Pracownik'} chce wziąć zmianę, choć nie podał(a) dyspozycyjności na te godziny.
                </p>
            )}

            {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
        </article>
    );
}
