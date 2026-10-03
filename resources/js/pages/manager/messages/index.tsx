import { EmptyState, Panel } from '@/components/panel';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatRelative } from '@/lib/format';
import { type Message } from '@/types';
import { Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

export default function ManagerMessages({ messages }: { messages: Message[] }) {
    return (
        <AppLayout
            title="Wiadomości"
            actions={
                <Button asChild>
                    <Link href={route('manager.messages.create')}>
                        <Plus /> Nowa
                    </Link>
                </Button>
            }
        >
            <Panel flush>
                {messages.length === 0 ? (
                    <EmptyState>Nie wysłano jeszcze żadnej wiadomości.</EmptyState>
                ) : (
                    <ul className="divide-y">
                        {messages.map((message) => (
                            <li key={message.id}>
                                <Link
                                    href={route('manager.messages.show', message.id)}
                                    className="hover:bg-muted/60 flex items-center gap-3 px-4 py-3"
                                >
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-sm font-medium">{message.title}</p>
                                        <p className="text-muted-foreground text-xs">{formatRelative(message.created_at)}</p>
                                    </div>
                                    {message.requires_acknowledgement ? (
                                        <span className="text-sm font-semibold tabular-nums">
                                            {message.acknowledged_count}/{message.recipients_count}
                                        </span>
                                    ) : (
                                        <span className="text-muted-foreground text-xs">{message.recipients_count} odbiorców</span>
                                    )}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </Panel>
        </AppLayout>
    );
}
