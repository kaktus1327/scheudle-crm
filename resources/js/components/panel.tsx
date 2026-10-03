import { cn } from '@/lib/utils';
import { type ReactNode } from 'react';

interface PanelProps {
    title?: ReactNode;
    description?: ReactNode;
    actions?: ReactNode;
    children: ReactNode;
    className?: string;

    flush?: boolean;
}

export function Panel({ title, description, actions, children, className, flush = false }: PanelProps) {
    return (
        <section className={cn('bg-background rounded-md border', className)}>
            {(title || actions) && (
                <header className="flex items-start justify-between gap-3 border-b px-4 py-3">
                    <div>
                        {title && <h3 className="text-sm font-semibold">{title}</h3>}
                        {description && <p className="text-muted-foreground mt-0.5 text-xs">{description}</p>}
                    </div>
                    {actions}
                </header>
            )}
            <div className={cn(!flush && 'p-4')}>{children}</div>
        </section>
    );
}

export function EmptyState({ children }: { children: ReactNode }) {
    return <p className="text-muted-foreground px-4 py-8 text-center text-sm">{children}</p>;
}
