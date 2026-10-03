import InputError from '@/components/input-error';
import { Panel } from '@/components/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatRelative } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type CinemaWeek } from '@/types';
import { useForm } from '@inertiajs/react';
import { Copy, Lock, X } from 'lucide-react';
import { type FormEventHandler } from 'react';

type Day = {
    date: string;
    unavailable: boolean;
    start_time: string;
    end_time: string;
};

interface Props {
    week: CinemaWeek;
    days: Day[];
    note: string | null;
    submittedAt: string | null;
    locked: boolean;
}

export default function AvailabilityEdit({ week, days, note, submittedAt, locked }: Props) {
    const { data, setData, put, processing, errors } = useForm({ days, note: note ?? '' });

    const updateDay = (index: number, patch: Partial<Day>) => {
        setData(
            'days',
            data.days.map((day, i) => (i === index ? { ...day, ...patch } : day)),
        );
    };

    const copyFirstToAll = () => {
        const first = data.days[0];
        setData(
            'days',
            data.days.map((day) => (day.unavailable ? day : { ...day, start_time: first.start_time, end_time: first.end_time })),
        );
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('availability.update'), { preserveScroll: true });
    };

    const fieldError = (index: number, field: string) => (errors as Record<string, string>)[`days.${index}.${field}`];

    return (
        <AppLayout title="Dyspozycyjność">
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4">
                <Panel
                    title={`Tydzień ${week.label}`}
                    description={
                        submittedAt ? `Wysłano ${formatRelative(submittedAt)} - możesz jeszcze poprawić` : 'Podaj godziny, w których możesz przyjść'
                    }
                    actions={
                        !locked && (
                            <Button type="button" variant="outline" size="sm" onClick={copyFirstToAll}>
                                <Copy /> Kopiuj pt
                            </Button>
                        )
                    }
                    flush
                >
                    {locked && (
                        <p className="bg-muted text-muted-foreground flex items-center gap-2 px-4 py-3 text-sm">
                            <Lock className="size-4" /> Grafik na ten tydzień jest już opublikowany.
                        </p>
                    )}
                    <InputError message={errors.days} className="px-4 pt-3" />

                    <ul className="divide-y">
                        {data.days.map((day, index) => (
                            <li
                                key={day.date}
                                className={cn('grid items-start gap-2 px-4 py-3 sm:grid-cols-[1fr_auto] sm:gap-4', day.unavailable && 'bg-muted/50')}
                            >
                                <span className="text-sm font-medium sm:pt-2">{formatDate(day.date)}</span>

                                <div className="grid grid-cols-[1fr_1fr_7rem] gap-2 sm:w-[22rem]">
                                    <div>
                                        <Label htmlFor={`start-${index}`} className="sr-only">
                                            Od
                                        </Label>
                                        <Input
                                            id={`start-${index}`}
                                            type="time"
                                            step={900}
                                            value={day.start_time}
                                            disabled={day.unavailable || locked}
                                            onChange={(e) => updateDay(index, { start_time: e.target.value })}
                                        />
                                        <InputError message={fieldError(index, 'start_time')} />
                                    </div>
                                    <div>
                                        <Label htmlFor={`end-${index}`} className="sr-only">
                                            Do
                                        </Label>
                                        <Input
                                            id={`end-${index}`}
                                            type="time"
                                            step={900}
                                            value={day.end_time}
                                            disabled={day.unavailable || locked}
                                            onChange={(e) => updateDay(index, { end_time: e.target.value })}
                                        />
                                        <InputError message={fieldError(index, 'end_time')} />
                                    </div>
                                    <Button
                                        type="button"
                                        variant={day.unavailable ? 'default' : 'outline'}
                                        disabled={locked}
                                        aria-pressed={day.unavailable}
                                        onClick={() => updateDay(index, { unavailable: !day.unavailable })}
                                        className="w-full"
                                    >
                                        {day.unavailable && <X />} Nie mogę
                                    </Button>
                                </div>
                            </li>
                        ))}
                    </ul>
                </Panel>

                <Panel title="Uwagi dla kierownika">
                    <Textarea
                        value={data.note}
                        disabled={locked}
                        onChange={(e) => setData('note', e.target.value)}
                        placeholder="Np. w sobotę mogę najpóźniej do 22:00"
                        maxLength={500}
                    />
                </Panel>

                {!locked && (
                    <Button type="submit" size="lg" className="w-full" disabled={processing}>
                        {submittedAt ? 'Zaktualizuj dyspozycyjność' : 'Wyślij dyspozycyjność'}
                    </Button>
                )}
            </form>
        </AppLayout>
    );
}
