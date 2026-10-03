import { MessageBody } from '@/components/message-body';
import { Panel } from '@/components/panel';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatRelative } from '@/lib/format';
import { MESSAGES_STALE_KEY } from '@/lib/messages';
import { type Message } from '@/types';
import { Link, router } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2 } from 'lucide-react';
import { useEffect } from 'react';

export default function MessageShow({ message }: { message: Message }) {
    useEffect(() => sessionStorage.setItem(MESSAGES_STALE_KEY, '1'), []);

    return (
        <AppLayout
            title="Wiadomość"
            actions={
                <Button variant="outline" size="sm" asChild>
                    <Link href={route('messages.index')}>
                        <ArrowLeft /> Wróć
                    </Link>
                </Button>
            }
        >
            <div className="mx-auto max-w-2xl space-y-4">
                <Panel>
                    <MessageBody message={message} />
                </Panel>

                {message.requires_acknowledgement &&
                    (message.acknowledged_at ? (
                        <p className="text-success flex items-center justify-center gap-2 text-sm font-medium">
                            <CheckCircle2 className="size-4" /> Zapoznano się {formatRelative(message.acknowledged_at)}
                        </p>
                    ) : (
                        <Button
                            size="lg"
                            className="w-full"
                            onClick={() => router.post(route('messages.acknowledge', message.id), {}, { preserveScroll: true })}
                        >
                            Zapoznałem/am się z wiadomością
                        </Button>
                    ))}
            </div>
        </AppLayout>
    );
}
