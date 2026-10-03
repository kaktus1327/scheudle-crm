import { cn } from '@/lib/utils';
import { LayoutGrid, List } from 'lucide-react';
import { useEffect, useState } from 'react';

export type ScheduleView = 'list' | 'table';

const STORAGE_KEY = 'schedule-view';

export function useScheduleView() {
    const [view, setView] = useState<ScheduleView>('list');

    useEffect(() => {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved === 'list' || saved === 'table') setView(saved);
    }, []);

    const change = (next: ScheduleView) => {
        setView(next);
        localStorage.setItem(STORAGE_KEY, next);
    };

    return [view, change] as const;
}

export function ScheduleViewToggle({
    view,
    onChange,
    className,
}: {
    view: ScheduleView;
    onChange: (view: ScheduleView) => void;
    className?: string;
}) {
    const options = [
        { value: 'list' as const, label: 'Lista', icon: List },
        { value: 'table' as const, label: 'Tabela', icon: LayoutGrid },
    ];

    return (
        <div className={cn('bg-muted inline-flex rounded-md p-0.5 lg:hidden', className)} role="group" aria-label="Widok grafiku">
            {options.map(({ value, label, icon: Icon }) => (
                <button
                    key={value}
                    type="button"
                    onClick={() => onChange(value)}
                    aria-pressed={view === value}
                    className={cn(
                        'flex items-center gap-1.5 rounded-sm px-3 py-1.5 text-xs font-medium',
                        view === value ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground',
                    )}
                >
                    <Icon className="size-3.5" /> {label}
                </button>
            ))}
        </div>
    );
}
