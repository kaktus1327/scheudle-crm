import { EmptyState, Panel } from '@/components/panel';
import { TradeCard } from '@/components/trade-card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type ShiftTrade } from '@/types';
import { router } from '@inertiajs/react';

export default function ManagerTrades({ pending, history }: { pending: ShiftTrade[]; history: ShiftTrade[] }) {
    const decide = (trade: ShiftTrade, approve: boolean) =>
        router.post(route('manager.trades.decide', trade.id), { approve }, { preserveScroll: true });

    return (
        <AppLayout title="Wymiany zmian">
            <div className="space-y-6">
                <section className="space-y-3">
                    <h3 className="text-sm font-semibold">Do akceptacji ({pending.length})</h3>
                    {pending.length === 0 ? (
                        <Panel>
                            <EmptyState>Brak wymian czekających na decyzję.</EmptyState>
                        </Panel>
                    ) : (
                        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            {pending.map((trade) => (
                                <TradeCard
                                    key={trade.id}
                                    trade={trade}
                                    actions={
                                        trade.can.decide && (
                                            <>
                                                <Button size="sm" onClick={() => decide(trade, true)}>
                                                    Akceptuj
                                                </Button>
                                                <Button size="sm" variant="outline" onClick={() => decide(trade, false)}>
                                                    Odrzuć
                                                </Button>
                                            </>
                                        )
                                    }
                                />
                            ))}
                        </div>
                    )}
                </section>

                <section className="space-y-3">
                    <h3 className="text-sm font-semibold">Ostatnie decyzje</h3>
                    {history.length === 0 ? (
                        <Panel>
                            <EmptyState>Brak historii.</EmptyState>
                        </Panel>
                    ) : (
                        <div className="grid gap-3 opacity-80 md:grid-cols-2 xl:grid-cols-3">
                            {history.map((trade) => (
                                <TradeCard key={trade.id} trade={trade} />
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
