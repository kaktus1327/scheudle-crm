import { EmptyState, Panel } from '@/components/panel';
import { StatCard } from '@/components/stat-card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatHours, formatMoney } from '@/lib/format';
import { type CinemaWeek, type Shift } from '@/types';
import { Link } from '@inertiajs/react';
import { CalendarClock, Clock, Mail, Wallet } from 'lucide-react';
import { Bar, BarChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

interface Props {
    stats: {
        hoursThisMonth: number;
        expectedPay: number;
        shiftsSplit: { weekday: number; weekend: number };
    };
    history: { month: string; label: string; hours: number }[];
    upcomingShifts: Shift[];
    pendingAcknowledgements: number;
    availabilitySubmitted: boolean;
    nextWeek: CinemaWeek;
}

export default function EmployeeDashboard({ stats, history, upcomingShifts, pendingAcknowledgements, availabilitySubmitted, nextWeek }: Props) {
    const totalShifts = stats.shiftsSplit.weekday + stats.shiftsSplit.weekend;

    return (
        <AppLayout title="Pulpit">
            <div className="space-y-4">
                {!availabilitySubmitted && (
                    <Notice icon={CalendarClock} text={`Nie wysłano jeszcze dyspozycyjności na tydzień ${nextWeek.label}.`}>
                        <Button asChild size="sm">
                            <Link href={route('availability.edit')}>Wyślij</Link>
                        </Button>
                    </Notice>
                )}
                {pendingAcknowledgements > 0 && (
                    <Notice icon={Mail} text={`Wiadomości do potwierdzenia: ${pendingAcknowledgements}`}>
                        <Button asChild size="sm" variant="outline">
                            <Link href={route('messages.index')}>Zobacz</Link>
                        </Button>
                    </Notice>
                )}

                <div className="grid grid-cols-2 gap-3 lg:grid-cols-3">
                    <StatCard label="Godziny w tym miesiącu" value={formatHours(stats.hoursThisMonth)} icon={Clock} />
                    <StatCard label="Przewidywana wypłata" value={formatMoney(stats.expectedPay)} hint="brutto, wg grafiku" icon={Wallet} />
                    <StatCard
                        className="col-span-2 lg:col-span-1"
                        label="Zmiany: tydzień / weekend"
                        value={`${stats.shiftsSplit.weekday} / ${stats.shiftsSplit.weekend}`}
                        hint={totalShifts ? `${Math.round((stats.shiftsSplit.weekend / totalShifts) * 100)}% zmian w weekend (pt-nd)` : 'brak zmian'}
                    />
                </div>

                <div className="grid gap-4 lg:grid-cols-5">
                    <Panel title="Godziny miesięcznie" description="Ostatnie 6 miesięcy" className="lg:col-span-3">
                        <div className="h-48">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={history} margin={{ top: 4, right: 4, bottom: 0, left: -24 }}>
                                    <XAxis dataKey="label" tickLine={false} axisLine={false} fontSize={12} />
                                    <YAxis tickLine={false} axisLine={false} fontSize={12} />
                                    <Tooltip
                                        cursor={{ fill: 'var(--muted)' }}
                                        formatter={(value) => [formatHours(Number(value)), 'Godziny']}
                                        contentStyle={{ borderRadius: 6, borderColor: 'var(--border)', fontSize: 12 }}
                                    />
                                    <Bar dataKey="hours" fill="var(--chart-1)" radius={[3, 3, 0, 0]} />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </Panel>

                    <Panel
                        title="Najbliższe zmiany"
                        className="lg:col-span-2"
                        flush
                        actions={
                            <Link href={route('schedule.mine')} className="text-primary text-xs font-medium">
                                Wszystkie
                            </Link>
                        }
                    >
                        {upcomingShifts.length === 0 ? (
                            <EmptyState>Brak zaplanowanych zmian.</EmptyState>
                        ) : (
                            <ul className="divide-y">
                                {upcomingShifts.map((shift) => (
                                    <li key={shift.id} className="flex items-center justify-between px-4 py-3 text-sm">
                                        <div>
                                            <p className="font-medium">{formatDate(shift.date)}</p>
                                            <p className="text-muted-foreground text-xs">{shift.position?.name}</p>
                                        </div>
                                        <span className="font-semibold tabular-nums">
                                            {shift.start_time}-{shift.end_time}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>
                </div>
            </div>
        </AppLayout>
    );
}

function Notice({ icon: Icon, text, children }: { icon: typeof Mail; text: string; children: React.ReactNode }) {
    return (
        <div className="border-l-primary bg-background flex items-center gap-3 rounded-md border border-l-4 p-3 text-sm">
            <Icon className="text-primary size-4 shrink-0" />
            <p className="flex-1">{text}</p>
            {children}
        </div>
    );
}
