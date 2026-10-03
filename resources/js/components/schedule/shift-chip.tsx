import { cn } from '@/lib/utils';
import { type Shift } from '@/types';
import { ArrowLeftRight } from 'lucide-react';

interface ShiftChipProps {
    shift: Shift;

    variant?: 'current' | 'original';
    highlightUserId?: number;
    className?: string;
}

export function ShiftChip({ shift, variant = 'current', highlightUserId, className }: ShiftChipProps) {
    const person = variant === 'original' ? shift.original_user : shift.user;
    const isOpen = !person;
    const isMine = !!highlightUserId && person?.id === highlightUserId;

    return (
        <div
            className={cn(
                'flex items-center gap-2 rounded-sm border px-2 py-1.5 text-xs',
                isOpen && 'border-open-foreground/20 bg-open text-open-foreground',
                isMine && 'border-primary bg-mine text-mine-foreground font-semibold',
                !isOpen && !isMine && 'bg-background',
                className,
            )}
        >
            <span className="min-w-0 flex-1 truncate">{isOpen ? 'Wolna zmiana' : person.name}</span>
            {shift.note && <ShiftNote note={shift.note} />}
            <span className="flex shrink-0 items-center gap-1 tabular-nums">
                {variant === 'current' && shift.was_traded && <ArrowLeftRight className="size-3" aria-label="Po wymianie" />}
                {shift.start_time}-{shift.end_time}
            </span>
        </div>
    );
}

export function ShiftNote({ note }: { note: string }) {
    return (
        <span className="bg-muted text-muted-foreground shrink-0 rounded-sm px-1 py-0.5 text-[10px] leading-none font-medium uppercase" title={note}>
            {note}
        </span>
    );
}
