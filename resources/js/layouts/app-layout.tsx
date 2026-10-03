import AppLogo from '@/components/app-logo';
import { FlashMessages } from '@/components/flash-messages';
import { UserMenu } from '@/components/user-menu';
import { isActive, navigationFor } from '@/lib/navigation';
import { cn } from '@/lib/utils';
import { type NavItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { type ReactNode, useState } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    title?: string;

    actions?: ReactNode;

    breadcrumbs?: { title: string; href: string }[];
}

export default function AppLayout({ children, title, actions, breadcrumbs }: AppLayoutProps) {
    const page = usePage<SharedData>();
    const items = navigationFor(page.props);
    const heading = title ?? breadcrumbs?.at(-1)?.title;

    return (
        <div className="bg-muted/40 min-h-svh">
            {heading && <Head title={heading} />}

            <DesktopSidebar items={items} currentUrl={page.url} />

            <div className="md:pl-60">
                <header className="bg-background sticky top-0 z-20 border-b">
                    <div className="mx-auto flex h-14 max-w-6xl items-center justify-between gap-3 px-4">
                        <div className="md:hidden">
                            <AppLogo />
                        </div>
                        <h1 className="hidden truncate text-base font-semibold md:block">{heading}</h1>
                        <UserMenu />
                    </div>
                </header>

                <main className="mx-auto w-full max-w-6xl px-4 pt-4 pb-28 md:pt-8 md:pb-12">
                    {(heading || actions) && (
                        <div className="mb-4 flex items-center justify-between gap-3 md:mb-6">
                            <h2 className="text-xl font-semibold tracking-tight md:hidden">{heading}</h2>
                            <div className="ml-auto flex items-center gap-2">{actions}</div>
                        </div>
                    )}
                    {children}
                </main>
            </div>

            <MobileBottomNav items={items} currentUrl={page.url} />
            <FlashMessages />
        </div>
    );
}

function Badge({ count }: { count?: number }) {
    if (!count) return null;

    return (
        <span className="bg-warning absolute -top-1 -right-2 min-w-4 rounded-sm px-1 text-center text-[10px] leading-4 font-semibold text-white">
            {count > 9 ? '9+' : count}
        </span>
    );
}

function DesktopSidebar({ items, currentUrl }: { items: NavItem[]; currentUrl: string }) {
    return (
        <aside className="bg-sidebar text-sidebar-foreground fixed inset-y-0 left-0 z-30 hidden w-60 flex-col md:flex">
            <div className="flex h-14 items-center px-4 text-white">
                <Link href={route('dashboard')}>
                    <AppLogo inverted />
                </Link>
            </div>
            <nav className="flex flex-1 flex-col gap-0.5 p-3">
                {items.map((item) => {
                    const active = isActive(item.url, currentUrl);

                    return (
                        <Link
                            key={item.url}
                            href={item.url}
                            prefetch
                            className={cn(
                                'flex h-10 items-center gap-3 rounded-md px-3 text-sm font-medium transition-colors',
                                active ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'hover:bg-sidebar-accent hover:text-white',
                            )}
                        >
                            <span className="relative">
                                <item.icon className="size-4" />
                                <Badge count={item.badge} />
                            </span>
                            {item.title}
                        </Link>
                    );
                })}
            </nav>
        </aside>
    );
}

function MobileBottomNav({ items, currentUrl }: { items: NavItem[]; currentUrl: string }) {
    const [moreOpen, setMoreOpen] = useState(false);
    const primary = items.filter((i) => i.primary);
    const secondary = items.filter((i) => !i.primary);
    const secondaryActive = secondary.some((i) => isActive(i.url, currentUrl));
    const secondaryBadge = secondary.reduce((sum, i) => sum + (i.badge ?? 0), 0);

    return (
        <>
            {moreOpen && (
                <div className="fixed inset-0 z-40 md:hidden" onClick={() => setMoreOpen(false)}>
                    <div className="absolute inset-0 bg-black/30" />
                    <div className="bg-background absolute inset-x-0 bottom-16 rounded-t-lg border-t p-3 pb-4" onClick={(e) => e.stopPropagation()}>
                        <div className="grid grid-cols-3 gap-2">
                            {secondary.map((item) => (
                                <Link
                                    key={item.url}
                                    href={item.url}
                                    onClick={() => setMoreOpen(false)}
                                    className={cn(
                                        'flex aspect-square flex-col items-center justify-center gap-2 rounded-md border text-xs font-medium',
                                        isActive(item.url, currentUrl) ? 'border-primary bg-primary text-primary-foreground' : 'bg-background',
                                    )}
                                >
                                    <span className="relative">
                                        <item.icon className="size-5" />
                                        <Badge count={item.badge} />
                                    </span>
                                    {item.title}
                                </Link>
                            ))}
                        </div>
                    </div>
                </div>
            )}

            <nav className="bg-background fixed inset-x-0 bottom-0 z-50 border-t pb-[env(safe-area-inset-bottom)] md:hidden">
                <div className="grid h-16 grid-cols-5">
                    {primary.map((item) => {
                        const active = isActive(item.url, currentUrl);

                        return (
                            <Link
                                key={item.url}
                                href={item.url}
                                prefetch
                                className={cn(
                                    'flex flex-col items-center justify-center gap-1 text-[11px] font-medium',
                                    active ? 'text-primary' : 'text-muted-foreground',
                                )}
                            >
                                <span className={cn('relative flex h-7 w-10 items-center justify-center rounded-md', active && 'bg-secondary')}>
                                    <item.icon className="size-5" />
                                    <Badge count={item.badge} />
                                </span>
                                {item.title}
                            </Link>
                        );
                    })}
                    <button
                        type="button"
                        onClick={() => setMoreOpen((v) => !v)}
                        className={cn(
                            'flex flex-col items-center justify-center gap-1 text-[11px] font-medium',
                            moreOpen || secondaryActive ? 'text-primary' : 'text-muted-foreground',
                        )}
                    >
                        <span
                            className={cn(
                                'relative flex h-7 w-10 items-center justify-center rounded-md',
                                (moreOpen || secondaryActive) && 'bg-secondary',
                            )}
                        >
                            {moreOpen ? <X className="size-5" /> : <Menu className="size-5" />}
                            <Badge count={secondaryBadge} />
                        </span>
                        Więcej
                    </button>
                </div>
            </nav>
        </>
    );
}
