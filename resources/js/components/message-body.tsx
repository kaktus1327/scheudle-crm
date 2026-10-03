import { formatDate, formatFileSize } from '@/lib/format';
import { type Message } from '@/types';
import { Paperclip } from 'lucide-react';

export function MessageBody({ message }: { message: Message }) {
    return (
        <article className="space-y-4">
            <header>
                <h2 className="text-lg font-semibold">{message.title}</h2>
                <p className="text-muted-foreground text-xs">
                    {message.author?.name} · {formatDate(message.created_at, 'd MMMM yyyy, HH:mm')}
                </p>
            </header>

            <div className="text-sm leading-relaxed whitespace-pre-line">{message.body}</div>

            {message.attachments && message.attachments.length > 0 && (
                <ul className="grid gap-2">
                    {message.attachments.map((file) => (
                        <li key={file.id}>
                            <a href={file.url} className="hover:bg-muted flex items-center gap-2 rounded-md border px-3 py-2 text-sm">
                                <Paperclip className="text-muted-foreground size-4" />
                                <span className="flex-1 truncate">{file.name}</span>
                                <span className="text-muted-foreground text-xs">{formatFileSize(file.size)}</span>
                            </a>
                        </li>
                    ))}
                </ul>
            )}
        </article>
    );
}
