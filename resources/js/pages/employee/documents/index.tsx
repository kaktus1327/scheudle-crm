import { DocumentStatus } from '@/components/document-status';
import { EmptyState, Panel } from '@/components/panel';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/lib/format';
import { type UserDocument } from '@/types';

export default function MyDocuments({ documents }: { documents: UserDocument[] }) {
    return (
        <AppLayout title="Moje dokumenty">
            <Panel description="Dokumenty przypisane przez kierownika. Przypomnimy Ci mailem, zanim stracą ważność." flush>
                {documents.length === 0 ? (
                    <EmptyState>Nie masz przypisanych dokumentów.</EmptyState>
                ) : (
                    <ul className="divide-y">
                        {documents.map((doc) => (
                            <li key={doc.id} className="flex items-center justify-between gap-3 px-4 py-3">
                                <div>
                                    <p className="text-sm font-medium">{doc.type?.name}</p>
                                    <p className="text-muted-foreground text-xs">Ważny do {formatDate(doc.valid_until, 'd MMMM yyyy')}</p>
                                </div>
                                <DocumentStatus document={doc} />
                            </li>
                        ))}
                    </ul>
                )}
            </Panel>
        </AppLayout>
    );
}
