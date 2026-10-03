import { cn } from '@/lib/utils';
import { type LucideIcon } from 'lucide-react';
import { type ReactNode } from 'react';

interface StatCardProps {
    label: string;
    value: ReactNode;
    hint?: ReactNode;
    icon?: LucideIcon;
    className?: string;
}

export function StatCard({ label, value, hint, icon: Icon, className }: StatCardProps) {
    return (
        <div className={cn('bg-background rounded-md border p-4', className)}>
            <div className="text-muted-foreground flex items-center justify-between text-xs font-medium tracking-wide uppercase">
                {label}
                {Icon && <Icon className="size-4" />}
            </div>
            <div className="mt-2 text-2xl font-semibold tabular-nums">{value}</div>
            {hint && <div className="text-muted-foreground mt-1 text-xs">{hint}</div>}
        </div>
    );
}
