import { EmptyState, Panel } from '@/components/panel';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { formatRelative } from '@/lib/format';
import { MESSAGES_STALE_KEY } from '@/lib/messages';
import { cn } from '@/lib/utils';
import { type Message } from '@/types';
import { Link, router } from '@inertiajs/react';
import { useEffect } from 'react';

export default function Messages({ messages }: { messages: Message[] }) {
    useEffect(() => {
        if (sessionStorage.getItem(MESSAGES_STALE_KEY)) {
            sessionStorage.removeItem(MESSAGES_STALE_KEY);
            router.reload({ only: ['messages', 'badges'] });
        }
    }, []);

    return (
        <AppLayout title="Wiadomości">
            <Panel flush>
                {messages.length === 0 ? (
                    <EmptyState>Brak wiadomości od kierownika.</EmptyState>
                ) : (
                    <ul className="divide-y">
                        {messages.map((message) => (
                            <li key={message.id}>
                                <Link href={route('messages.show', message.id)} className="hover:bg-muted/60 flex gap-3 px-4 py-3">
                                    <span className={cn('mt-1.5 size-2 shrink-0 rounded-full', message.read_at ? 'bg-transparent' : 'bg-primary')} />
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center justify-between gap-2">
                                            <p className={cn('truncate text-sm', !message.read_at && 'font-semibold')}>{message.title}</p>
                                            <span className="text-muted-foreground shrink-0 text-xs">{formatRelative(message.created_at)}</span>
                                        </div>
                                        <p className="text-muted-foreground mt-0.5 line-clamp-2 text-xs">{message.excerpt}</p>
                                        {message.requires_acknowledgement && (
                                            <div className="mt-2">
                                                {message.acknowledged_at ? (
                                                    <Badge variant="success">Potwierdzono</Badge>
                                                ) : (
                                                    <Badge variant="warning">Wymaga potwierdzenia</Badge>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </Panel>
        </AppLayout>
    );
}
