import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { CheckCircle2, TriangleAlert, X } from 'lucide-react';
import { useEffect, useState } from 'react';

const GLOBAL_ERROR_KEYS = ['trade', 'shift', 'availability', 'position', 'date'];

export function FlashMessages() {
    const { flash, errors } = usePage<SharedData & { errors: Record<string, string> }>().props;
    const [visible, setVisible] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

    useEffect(() => {
        const globalError = GLOBAL_ERROR_KEYS.map((key) => errors[key]).find(Boolean);

        if (flash.success) setVisible({ type: 'success', text: flash.success });
        else if (flash.error) setVisible({ type: 'error', text: flash.error });
        else if (globalError) setVisible({ type: 'error', text: globalError });

        const timer = setTimeout(() => setVisible(null), 5000);

        return () => clearTimeout(timer);
    }, [flash, errors]);

    if (!visible) return null;

    const Icon = visible.type === 'success' ? CheckCircle2 : TriangleAlert;

    return (
        <div className="fixed inset-x-4 bottom-20 z-50 md:inset-x-auto md:right-6 md:bottom-6 md:w-96">
            <div
                role="status"
                className={cn(
                    'bg-background flex items-start gap-3 rounded-md border p-3 text-sm shadow-sm',
                    visible.type === 'success' ? 'border-l-success border-l-4' : 'border-l-destructive border-l-4',
                )}
            >
                <Icon className={cn('mt-0.5 size-4 shrink-0', visible.type === 'success' ? 'text-success' : 'text-destructive')} />
                <p className="flex-1">{visible.text}</p>
                <button type="button" onClick={() => setVisible(null)} className="text-muted-foreground" aria-label="Zamknij">
                    <X className="size-4" />
                </button>
            </div>
        </div>
    );
}
