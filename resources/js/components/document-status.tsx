import { Badge } from '@/components/ui/badge';
import { type UserDocument } from '@/types';

export function DocumentStatus({ document }: { document: UserDocument }) {
    if (document.days_left < 0) return <Badge variant="destructive">Nieważny</Badge>;
    if (document.expiring_soon) return <Badge variant="warning">{document.days_left} dni</Badge>;

    return <Badge variant="success">Ważny</Badge>;
}
