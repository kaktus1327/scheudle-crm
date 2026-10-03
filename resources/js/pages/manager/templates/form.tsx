import InputError from '@/components/input-error';
import { Panel } from '@/components/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import AppLayout from '@/layouts/app-layout';
import { type Position } from '@/types';
import { router, useForm } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { type FormEventHandler } from 'react';

type TemplateShift = {
    position_id: number;
    day_offset: number;
    start_time: string;
    end_time: string;
    note: string;
};

interface Props {
    template: { id: number; name: string; description: string | null; is_system: boolean; shifts: TemplateShift[] } | null;
    positions: Position[];
}

const DAYS = ['Piątek', 'Sobota', 'Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek'];

export default function TemplateForm({ template, positions }: Props) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: template?.name ?? '',
        description: template?.description ?? '',
        shifts: template?.shifts ?? ([] as TemplateShift[]),
    });

    const addShift = (day: number) =>
        setData('shifts', [...data.shifts, { position_id: positions[0]?.id, day_offset: day, start_time: '16:00', end_time: '23:00', note: '' }]);

    const updateShift = (index: number, patch: Partial<TemplateShift>) =>
        setData(
            'shifts',
            data.shifts.map((s, i) => (i === index ? { ...s, ...patch } : s)),
        );

    const removeShift = (index: number) =>
        setData(
            'shifts',
            data.shifts.filter((_, i) => i !== index),
        );

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (template) put(route('manager.templates.update', template.id), { preserveScroll: true });
        else post(route('manager.templates.store'));
    };

    return (
        <AppLayout title={template ? `Szablon: ${template.name}` : 'Nowy szablon'}>
            <form onSubmit={submit} className="space-y-4">
                <Panel>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Nazwa</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Np. Tydzień premierowy"
                                required
                            />
                            <InputError message={errors.name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="description">Opis</Label>
                            <Input id="description" value={data.description} onChange={(e) => setData('description', e.target.value)} />
                        </div>
                    </div>
                </Panel>

                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    {DAYS.map((label, day) => (
                        <Panel
                            key={day}
                            title={label}
                            actions={
                                <Button type="button" variant="ghost" size="icon-sm" onClick={() => addShift(day)} aria-label="Dodaj zmianę">
                                    <Plus />
                                </Button>
                            }
                        >
                            <div className="space-y-2">
                                {data.shifts.map((shift, index) =>
                                    shift.day_offset !== day ? null : (
                                        <div key={index} className="grid grid-cols-[1fr_auto] gap-2 rounded-md border p-2">
                                            <NativeSelect
                                                value={shift.position_id}
                                                onChange={(e) => updateShift(index, { position_id: Number(e.target.value) })}
                                                className="h-9"
                                            >
                                                {positions.map((p) => (
                                                    <option key={p.id} value={p.id}>
                                                        {p.name}
                                                    </option>
                                                ))}
                                            </NativeSelect>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="size-9"
                                                onClick={() => removeShift(index)}
                                                aria-label="Usuń"
                                            >
                                                <Trash2 />
                                            </Button>
                                            <div className="col-span-2 grid grid-cols-2 gap-2">
                                                <Input
                                                    type="time"
                                                    step={900}
                                                    value={shift.start_time}
                                                    onChange={(e) => updateShift(index, { start_time: e.target.value })}
                                                    className="h-9"
                                                />
                                                <Input
                                                    type="time"
                                                    step={900}
                                                    value={shift.end_time}
                                                    onChange={(e) => updateShift(index, { end_time: e.target.value })}
                                                    className="h-9"
                                                />
                                            </div>
                                            <Input
                                                value={shift.note}
                                                maxLength={50}
                                                placeholder="Dopisek, np. KIOSK"
                                                onChange={(e) => updateShift(index, { note: e.target.value })}
                                                className="col-span-2 h-9"
                                            />
                                        </div>
                                    ),
                                )}
                                {!data.shifts.some((s) => s.day_offset === day) && <p className="text-muted-foreground text-xs">Brak zmian</p>}
                            </div>
                        </Panel>
                    ))}
                </div>

                <div className="flex flex-wrap gap-2">
                    <Button type="submit" disabled={processing}>
                        Zapisz szablon
                    </Button>
                    {template && !template.is_system && (
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => window.confirm('Usunąć szablon?') && router.delete(route('manager.templates.destroy', template.id))}
                        >
                            <Trash2 /> Usuń
                        </Button>
                    )}
                </div>
            </form>
        </AppLayout>
    );
}
