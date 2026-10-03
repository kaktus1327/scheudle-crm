import { Panel } from '@/components/panel';
import { StatCard } from '@/components/stat-card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatHours, formatMoney } from '@/lib/format';
import { type CinemaWeek } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowLeftRight, CalendarClock, Clock, FileBadge, Mail, Wallet } from 'lucide-react';

interface Props {
    team: { id: number; name: string; hours: number; pay: number }[];
    totals: { hours: number; pay: number; employees: number };
    lastMessage: { id: number; title: string; requires_acknowledgement: boolean; recipients: number; acknowledged: number } | null;
    pendingTrades: number;
    expiringDocuments: number;
    availability: { week: CinemaWeek; submitted: number };
}

export default function ManagerDashboard({ team, totals, lastMessage, pendingTrades, expiringDocuments, availability }: Props) {
    const maxHours = Math.max(1, ...team.map((m) => m.hours));
    const ackPercent = lastMessage && lastMessage.recipients > 0 ? Math.round((lastMessage.acknowledged / lastMessage.recipients) * 100) : 0;

    return (
        <AppLayout title="Pulpit kierownika">
            <div className="space-y-4">
                <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <StatCard label="Godziny zespołu" value={formatHours(totals.hours)} hint="w tym miesiącu" icon={Clock} />
                    <StatCard label="Do wypłaty" value={formatMoney(totals.pay)} hint="brutto, wg grafiku" icon={Wallet} />
                    <Link href={route('manager.trades.index')}>
                        <StatCard label="Wymiany do akceptacji" value={pendingTrades} icon={ArrowLeftRight} className="hover:border-primary h-full" />
                    </Link>
                    <Link href={route('manager.documents.index', { within: 30 })}>
                        <StatCard
                            label="Kończące się dokumenty"
                            value={expiringDocuments}
                            hint="w ciągu 30 dni"
                            icon={FileBadge}
                            className="hover:border-primary h-full"
                        />
                    </Link>
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Panel title="Ostatnia wiadomość" className="lg:col-span-1">
                        {lastMessage ? (
                            <div className="space-y-3">
                                <p className="text-sm font-medium">{lastMessage.title}</p>
                                {lastMessage.requires_acknowledgement ? (
                                    <>
                                        <div className="bg-muted h-2 overflow-hidden rounded-sm">
                                            <div className="bg-primary h-full" style={{ width: `${ackPercent}%` }} />
                                        </div>
                                        <p className="text-muted-foreground text-xs">
                                            Zapoznało się {lastMessage.acknowledged} z {lastMessage.recipients} osób ({ackPercent}%)
                                        </p>
                                    </>
                                ) : (
                                    <p className="text-muted-foreground text-xs">Nie wymaga potwierdzenia</p>
                                )}
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={route('manager.messages.show', lastMessage.id)}>Szczegóły</Link>
                                </Button>
                            </div>
                        ) : (
                            <Button size="sm" asChild>
                                <Link href={route('manager.messages.create')}>
                                    <Mail /> Napisz pierwszą wiadomość
                                </Link>
                            </Button>
                        )}
                    </Panel>

                    <Panel title="Dyspozycyjność" description={`Tydzień ${availability.week.label}`}>
                        <div className="flex items-end gap-2">
                            <span className="text-3xl font-semibold tabular-nums">{availability.submitted}</span>
                            <span className="text-muted-foreground pb-1 text-sm">/ {totals.employees} osób wysłało</span>
                        </div>
                        <Button size="sm" className="mt-3" asChild>
                            <Link href={route('manager.schedules.index')}>
                                <CalendarClock /> Układaj grafik
                            </Link>
                        </Button>
                    </Panel>

                    <Panel title="Godziny pracowników" description="Bieżący miesiąc" flush className="lg:row-span-2">
                        <ul className="divide-y">
                            {team.map((member) => (
                                <li key={member.id} className="px-4 py-2.5">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="font-medium">{member.name}</span>
                                        <span className="tabular-nums">{formatHours(member.hours)}</span>
                                    </div>
                                    <div className="mt-1.5 flex items-center gap-2">
                                        <div className="bg-muted h-1.5 flex-1 overflow-hidden rounded-sm">
                                            <div className="bg-primary h-full" style={{ width: `${(member.hours / maxHours) * 100}%` }} />
                                        </div>
                                        <span className="text-muted-foreground w-20 text-right text-xs tabular-nums">{formatMoney(member.pay)}</span>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </Panel>
                </div>
            </div>
        </AppLayout>
    );
}
