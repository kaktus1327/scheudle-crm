import InputError from '@/components/input-error';
import { EmptyState, Panel } from '@/components/panel';
import { ShiftSummary, TradeCard } from '@/components/trade-card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { formatShortDate } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type Person, type SharedData, type Shift, type ShiftTrade, type TradeType } from '@/types';
import { router, useForm, usePage } from '@inertiajs/react';
import { Clock } from 'lucide-react';
import { type FormEventHandler, useState } from 'react';

interface Props {
    market: ShiftTrade[];
    openShifts: (Shift & { claim: 'mine' | 'taken' | null })[];
    incoming: ShiftTrade[];
    outgoing: ShiftTrade[];
    myShifts: Shift[];
    colleagues: Person[];
    colleagueShifts: Shift[];
}

const tabs = [
    { id: 'market', label: 'Giełda' },
    { id: 'incoming', label: 'Do mnie' },
    { id: 'outgoing', label: 'Moje' },
    { id: 'new', label: 'Nowa' },
] as const;

type Tab = (typeof tabs)[number]['id'];

export default function Trades(props: Props) {
    const { auth } = usePage<SharedData>().props;
    const [tab, setTab] = useState<Tab>(props.incoming.length > 0 ? 'incoming' : 'market');
    const post = (url: string, data: Record<string, string | number | boolean> = {}) => router.post(url, data, { preserveScroll: true });

    return (
        <AppLayout title="Giełda zmian">
            <div className="space-y-4">
                <div className="bg-background grid grid-cols-4 gap-1 rounded-md border p-1">
                    {tabs.map((t) => {
                        const count = t.id === 'incoming' ? props.incoming.length : 0;

                        return (
                            <button
                                key={t.id}
                                type="button"
                                onClick={() => setTab(t.id)}
                                className={cn(
                                    'h-9 rounded-sm text-sm font-medium',
                                    tab === t.id ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                                )}
                            >
                                {t.label}
                                {count > 0 && ` (${count})`}
                            </button>
                        );
                    })}
                </div>

                {tab === 'market' && (
                    <div className="space-y-4">
                        <Panel title="Wolne zmiany w grafiku" description="Kto pierwszy, ten lepszy - po zgłoszeniu decyduje kierownik" flush>
                            {props.openShifts.length === 0 ? (
                                <EmptyState>Brak wolnych zmian.</EmptyState>
                            ) : (
                                <ul className="divide-y">
                                    {props.openShifts.map((shift) => (
                                        <li
                                            key={shift.id}
                                            className={cn('flex items-center justify-between gap-3 px-4 py-3', !shift.claim && 'bg-open/40')}
                                        >
                                            <ShiftSummary shift={shift} />
                                            {shift.claim ? (
                                                <PendingLabel mine={shift.claim === 'mine'} />
                                            ) : (
                                                <Button size="sm" onClick={() => post(route('shifts.claim', shift.id))}>
                                                    Biorę
                                                </Button>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Panel>

                        <Panel title="Oddane na giełdę" flush>
                            <div className="grid gap-3 p-3 sm:grid-cols-2">
                                {props.market.length === 0 && <EmptyState>Nikt nie oddaje teraz zmian.</EmptyState>}
                                {props.market.map((trade) => (
                                    <TradeCard
                                        key={trade.id}
                                        trade={trade}
                                        actions={
                                            trade.status === 'pending_manager' ? (
                                                <PendingLabel mine={trade.claimer?.id === auth.user.id} />
                                            ) : (
                                                trade.can.claim && (
                                                    <Button size="sm" onClick={() => post(route('trades.claim', trade.id))}>
                                                        Biorę
                                                    </Button>
                                                )
                                            )
                                        }
                                    />
                                ))}
                            </div>
                        </Panel>
                    </div>
                )}

                {tab === 'incoming' && (
                    <TradeList
                        trades={props.incoming}
                        empty="Nikt nie wysłał Ci propozycji."
                        actions={(trade) =>
                            trade.can.respond && (
                                <>
                                    <Button size="sm" onClick={() => post(route('trades.respond', trade.id), { accept: true })}>
                                        Akceptuję
                                    </Button>
                                    <Button size="sm" variant="outline" onClick={() => post(route('trades.respond', trade.id), { accept: false })}>
                                        Odrzucam
                                    </Button>
                                </>
                            )
                        }
                    />
                )}

                {tab === 'outgoing' && (
                    <TradeList
                        trades={props.outgoing}
                        empty="Nie masz jeszcze żadnych propozycji."
                        actions={(trade) =>
                            trade.can.cancel && (
                                <Button size="sm" variant="outline" onClick={() => post(route('trades.cancel', trade.id))}>
                                    Anuluj
                                </Button>
                            )
                        }
                    />
                )}

                {tab === 'new' && <NewTradeForm {...props} onDone={() => setTab('outgoing')} />}
            </div>
        </AppLayout>
    );
}

function PendingLabel({ mine }: { mine: boolean }) {
    return (
        <span className="text-muted-foreground flex shrink-0 items-center gap-1.5 text-right text-xs font-medium">
            <Clock className="size-3.5 shrink-0" />
            {mine ? 'Zgłoszono - czeka na akceptację kierownika' : 'Zajęta - czeka na akceptację'}
        </span>
    );
}

function TradeList({ trades, empty, actions }: { trades: ShiftTrade[]; empty: string; actions: (t: ShiftTrade) => React.ReactNode }) {
    if (trades.length === 0) {
        return (
            <Panel>
                <EmptyState>{empty}</EmptyState>
            </Panel>
        );
    }

    return (
        <div className="grid gap-3 sm:grid-cols-2">
            {trades.map((trade) => (
                <TradeCard key={trade.id} trade={trade} actions={actions(trade)} />
            ))}
        </div>
    );
}

function NewTradeForm({ myShifts, colleagues, colleagueShifts, onDone }: Props & { onDone: () => void }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        type: 'giveaway' as TradeType,
        offered_shift_id: '',
        target_user_id: '',
        requested_shift_id: '',
        note: '',
    });

    const theirShifts = colleagueShifts.filter((s) => String(s.user?.id) === data.target_user_id);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('trades.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onDone();
            },
        });
    };

    const types: { value: TradeType; label: string; hint: string }[] = [
        { value: 'giveaway', label: 'Oddaję', hint: 'konkretnej osobie' },
        { value: 'swap', label: 'Zamieniam', hint: 'na jej zmianę' },
        { value: 'open_offer', label: 'Na giełdę', hint: 'dla wszystkich' },
    ];

    return (
        <Panel title="Nowa propozycja">
            <form onSubmit={submit} className="space-y-4">
                <div className="grid grid-cols-3 gap-2">
                    {types.map((t) => (
                        <button
                            key={t.value}
                            type="button"
                            onClick={() => setData('type', t.value)}
                            className={cn(
                                'rounded-md border p-2 text-left',
                                data.type === t.value ? 'border-primary bg-secondary' : 'hover:bg-muted',
                            )}
                        >
                            <p className="text-sm font-semibold">{t.label}</p>
                            <p className="text-muted-foreground text-xs">{t.hint}</p>
                        </button>
                    ))}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="offered">Moja zmiana</Label>
                    <NativeSelect id="offered" value={data.offered_shift_id} onChange={(e) => setData('offered_shift_id', e.target.value)}>
                        <option value="">Wybierz zmianę</option>
                        {myShifts.map((s) => (
                            <option key={s.id} value={s.id}>
                                {formatShortDate(s.date)} {s.start_time}-{s.end_time} ({s.position?.name})
                            </option>
                        ))}
                    </NativeSelect>
                    <InputError message={errors.offered_shift_id} />
                </div>

                {data.type !== 'open_offer' && (
                    <div className="grid gap-2">
                        <Label htmlFor="target">Do kogo</Label>
                        <NativeSelect
                            id="target"
                            value={data.target_user_id}
                            onChange={(e) => setData({ ...data, target_user_id: e.target.value, requested_shift_id: '' })}
                        >
                            <option value="">Wybierz osobę</option>
                            {colleagues.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </NativeSelect>
                        <InputError message={errors.target_user_id} />
                    </div>
                )}

                {data.type === 'swap' && (
                    <div className="grid gap-2">
                        <Label htmlFor="requested">Za jej/jego zmianę</Label>
                        <NativeSelect
                            id="requested"
                            value={data.requested_shift_id}
                            disabled={!data.target_user_id}
                            onChange={(e) => setData('requested_shift_id', e.target.value)}
                        >
                            <option value="">{data.target_user_id ? 'Wybierz zmianę' : 'Najpierw wybierz osobę'}</option>
                            {theirShifts.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {formatShortDate(s.date)} {s.start_time}-{s.end_time} ({s.position?.name})
                                </option>
                            ))}
                        </NativeSelect>
                        <InputError message={errors.requested_shift_id} />
                    </div>
                )}

                <div className="grid gap-2">
                    <Label htmlFor="note">Wiadomość (opcjonalnie)</Label>
                    <Textarea id="note" value={data.note} onChange={(e) => setData('note', e.target.value)} className="min-h-16" maxLength={500} />
                </div>

                <Button type="submit" className="w-full" disabled={processing}>
                    Wyślij propozycję
                </Button>
            </form>
        </Panel>
    );
}
