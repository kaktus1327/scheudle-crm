import { Button } from '@/components/ui/button';
import { type CinemaWeek } from '@/types';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

export function WeekSwitcher({ week, prev, next, routeName }: { week: CinemaWeek; prev: string; next: string; routeName: string }) {
    return (
        <div className="flex items-center gap-2">
            <Button variant="outline" size="icon" asChild>
                <Link href={route(routeName, { week: prev })} preserveScroll aria-label="Poprzedni tydzień">
                    <ChevronLeft />
                </Link>
            </Button>
            <div className="min-w-36 text-center">
                <p className="text-muted-foreground text-[11px] tracking-wide uppercase">Tydzień pt-czw</p>
                <p className="text-sm font-semibold tabular-nums">{week.label}</p>
            </div>
            <Button variant="outline" size="icon" asChild>
                <Link href={route(routeName, { week: next })} preserveScroll aria-label="Następny tydzień">
                    <ChevronRight />
                </Link>
            </Button>
        </div>
    );
}
