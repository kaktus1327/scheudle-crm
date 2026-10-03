import { MessageBody } from '@/components/message-body';
import { Panel } from '@/components/panel';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { formatRelative } from '@/lib/format';
import { type Message } from '@/types';
import { useState } from 'react';

interface Recipient {
    id: number;
    name: string;
    read_at: string | null;
    acknowledged_at: string | null;
}

export default function ManagerMessageShow({ message, recipients }: { message: Message; recipients: Recipient[] }) {
    const [filter, setFilter] = useState<'all' | 'pending'>('all');
    const acknowledged = recipients.filter((r) => r.acknowledged_at).length;
    const visible =
        filter === 'pending' ? recipients.filter((r) => (message.requires_acknowledgement ? !r.acknowledged_at : !r.read_at)) : recipients;

    return (
        <AppLayout title="Wiadomość">
            <div className="grid gap-4 lg:grid-cols-[1fr_320px]">
                <Panel>
                    <MessageBody message={message} />
                </Panel>

                <Panel
                    title={
                        message.requires_acknowledgement ? `Zapoznało się ${acknowledged}/${recipients.length}` : `Odbiorcy (${recipients.length})`
                    }
                    actions={
                        <button
                            type="button"
                            className="text-primary text-xs font-medium"
                            onClick={() => setFilter(filter === 'all' ? 'pending' : 'all')}
                        >
                            {filter === 'all' ? 'Tylko brakujący' : 'Wszyscy'}
                        </button>
                    }
                    flush
                >
                    <ul className="divide-y">
                        {visible.map((r) => (
                            <li key={r.id} className="flex items-center justify-between px-4 py-2.5 text-sm">
                                <span>{r.name}</span>
                                {message.requires_acknowledgement ? (
                                    r.acknowledged_at ? (
                                        <Badge variant="success" title={formatRelative(r.acknowledged_at)}>
                                            Zapoznał(a) się
                                        </Badge>
                                    ) : (
                                        <Badge variant="warning">{r.read_at ? 'Przeczytane' : 'Brak'}</Badge>
                                    )
                                ) : (
                                    <Badge variant={r.read_at ? 'secondary' : 'outline'}>{r.read_at ? 'Przeczytane' : 'Nieprzeczytane'}</Badge>
                                )}
                            </li>
                        ))}
                    </ul>
                </Panel>
            </div>
        </AppLayout>
    );
}
