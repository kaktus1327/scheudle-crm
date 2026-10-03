import { cn } from '@/lib/utils';
import AppLogoIcon from './app-logo-icon';

export default function AppLogo({ inverted = false }: { inverted?: boolean }) {
    return (
        <div className="flex items-center gap-2">
            <div
                className={cn(
                    'flex size-8 items-center justify-center rounded-md',
                    inverted ? 'text-primary bg-white' : 'bg-primary text-primary-foreground',
                )}
            >
                <AppLogoIcon className="size-5" />
            </div>
            <span className="text-sm font-semibold tracking-tight">Kino Grafik</span>
        </div>
    );
}
