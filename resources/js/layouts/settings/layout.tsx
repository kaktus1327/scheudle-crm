import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';

const items = [
    { title: 'Profil', url: '/settings/profile' },
    { title: 'Hasło', url: '/settings/password' },
];

export default function SettingsLayout({ children }: { children: React.ReactNode }) {
    const { url } = usePage();

    return (
        <div className="flex flex-col gap-6 lg:flex-row lg:gap-12">
            <nav className="flex gap-2 lg:w-48 lg:flex-col">
                {items.map((item) => (
                    <Link
                        key={item.url}
                        href={item.url}
                        prefetch
                        className={cn(
                            'flex h-9 items-center rounded-md px-3 text-sm font-medium',
                            url.startsWith(item.url) ? 'bg-primary text-primary-foreground' : 'bg-background hover:bg-accent border',
                        )}
                    >
                        {item.title}
                    </Link>
                ))}
            </nav>

            <section className="bg-background max-w-xl flex-1 space-y-12 rounded-md border p-4 md:p-6">{children}</section>
        </div>
    );
}
