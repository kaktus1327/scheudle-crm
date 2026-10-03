import { ShiftNote } from '@/components/schedule/shift-chip';
import { ScheduleViewToggle, useScheduleView } from '@/components/schedule/view-toggle';
import { WeekGrid } from '@/components/schedule/week-grid';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import AppLayout from '@/layouts/app-layout';
import { formatDate, formatHours } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type Position, type Schedule, type Shift, type WeekDay } from '@/types';
import { DndContext, type DragEndEvent, PointerSensor, TouchSensor, useDraggable, useDroppable, useSensor, useSensors } from '@dnd-kit/core';
import { router } from '@inertiajs/react';
import { CalendarCheck, GripVertical, Plus, Save, Sparkles, Trash2, TriangleAlert, X } from 'lucide-react';
import { type ReactNode, useState } from 'react';

interface AvailabilityDay {
    date: string;
    unavailable: boolean;
    start_time: string | null;
    end_time: string | null;
}

interface BuilderEmployee {
    id: number;
    name: string;
    positions: Position[];
    assigned_hours: number;
    available_hours: number;
    availability: AvailabilityDay[] | null;
    availability_note: string | null;
}

interface Props {
    schedule: Schedule;
    employees: BuilderEmployee[];
    positions: Position[];
    templates: { id: number; name: string; is_system: boolean }[];
}

type Conflict = { shift: Shift; employee: BuilderEmployee; message: string };

export default function ScheduleBuilder({ schedule, employees, positions, templates }: Props) {
    const shifts = schedule.shifts ?? [];
    const isDraft = schedule.status === 'draft';

    const [preview, setPreview] = useState<BuilderEmployee | null>(null);
    const [editing, setEditing] = useState<Shift | null>(null);
    const [adding, setAdding] = useState<string | null>(null);
    const [conflict, setConflict] = useState<Conflict | null>(null);
    const [templateId, setTemplateId] = useState('');
    const [view, setView] = useScheduleView();

    const droppable = (shift: Shift) => <DroppableShift key={shift.id} shift={shift} onClick={() => setEditing(shift)} />;

    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 6 } }),
        useSensor(TouchSensor, { activationConstraint: { delay: 200, tolerance: 6 } }),
    );

    const assign = (shift: Shift, employee: BuilderEmployee | null, force = false) => {
        router.put(
            route('manager.shifts.assign', shift.id),
            { user_id: employee?.id ?? null, force },
            {
                preserveScroll: true,
                onError: (errors) => {
                    if (errors.availability && employee) {
                        setConflict({ shift, employee, message: errors.availability });
                    }
                },
                onSuccess: () => setEditing(null),
            },
        );
    };

    const onDragEnd = ({ active, over }: DragEndEvent) => {
        if (!over) return;
        const employee = employees.find((e) => e.id === active.data.current?.employeeId);
        const shift = shifts.find((s) => s.id === over.data.current?.shiftId);
        if (employee && shift) assign(shift, employee);
    };

    return (
        <AppLayout title={`Grafik ${schedule.week.label}`}>
            <div className="space-y-4">
                <Toolbar
                    schedule={schedule}
                    isDraft={isDraft}
                    templates={templates}
                    templateId={templateId}
                    setTemplateId={setTemplateId}
                    openCount={shifts.filter((s) => s.is_open).length}
                />

                <DndContext sensors={sensors} onDragEnd={onDragEnd}>
                    <div className="grid gap-4 lg:grid-cols-[240px_minmax(0,1fr)]">
                        <aside className="bg-background h-fit rounded-md border lg:sticky lg:top-20">
                            <header className="border-b px-3 py-2">
                                <h3 className="text-sm font-semibold">Pracownicy</h3>
                                <p className="text-muted-foreground text-xs">Przeciągnij na zmianę, kliknij - dyspozycyjność</p>
                            </header>
                            <ul className="flex gap-2 overflow-x-auto p-2 lg:max-h-[70vh] lg:flex-col lg:overflow-y-auto">
                                {employees.map((employee) => (
                                    <DraggableEmployee key={employee.id} employee={employee} onClick={() => setPreview(employee)} />
                                ))}
                            </ul>
                        </aside>

                        <div className="min-w-0 space-y-3">
                            <ScheduleViewToggle view={view} onChange={setView} />

                            {view === 'list' && (
                                <div className="grid gap-3 md:grid-cols-2 lg:hidden">
                                    {schedule.week.days.map((day) => (
                                        <DayCard
                                            key={day.date}
                                            day={day}
                                            shifts={shifts.filter((s) => s.date === day.date)}
                                            positions={positions}
                                            renderShift={droppable}
                                            onAdd={() => setAdding(day.date)}
                                        />
                                    ))}
                                </div>
                            )}

                            <div className={cn(view === 'list' && 'hidden lg:block')}>
                                <WeekGrid
                                    days={schedule.week.days}
                                    positions={positions}
                                    shifts={shifts}
                                    renderShift={droppable}
                                    columnClassName="min-w-44"
                                    renderDayAction={(day) => (
                                        <Button variant="ghost" size="icon-sm" onClick={() => setAdding(day.date)} aria-label="Dodaj zmianę">
                                            <Plus />
                                        </Button>
                                    )}
                                />
                            </div>
                        </div>
                    </div>
                </DndContext>
            </div>

            <AvailabilityDialog employee={preview} days={schedule.week.days.map((d) => d.date)} onClose={() => setPreview(null)} />

            <ShiftDialog shift={editing} employees={employees} onClose={() => setEditing(null)} onAssign={assign} />

            <AddShiftDialog scheduleId={schedule.id} date={adding} positions={positions} onClose={() => setAdding(null)} />

            <Dialog open={!!conflict} onOpenChange={(open) => !open && setConflict(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <TriangleAlert className="text-warning size-5" /> Poza dyspozycyjnością
                        </DialogTitle>
                        <DialogDescription>{conflict?.message} Dodać mimo to?</DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="gap-2">
                        <Button variant="outline" onClick={() => setConflict(null)}>
                            Odrzuć
                        </Button>
                        <Button
                            onClick={() => {
                                if (conflict) assign(conflict.shift, conflict.employee, true);
                                setConflict(null);
                            }}
                        >
                            Dodaj mimo to
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}

function Toolbar({
    schedule,
    isDraft,
    templates,
    templateId,
    setTemplateId,
    openCount,
}: {
    schedule: Schedule;
    isDraft: boolean;
    templates: Props['templates'];
    templateId: string;
    setTemplateId: (id: string) => void;
    openCount: number;
}) {
    const post = (name: string, data: Record<string, string | number | boolean> = {}) =>
        router.post(route(name, schedule.id), data, { preserveScroll: true });

    const saveTemplate = () => {
        const name = window.prompt('Nazwa nowego szablonu');
        if (name) post('manager.schedules.save-template', { name });
    };

    return (
        <div className="bg-background flex flex-wrap items-center gap-2 rounded-md border p-2">
            <Badge variant={isDraft ? 'open' : 'success'}>{schedule.status_label}</Badge>
            {openCount > 0 && <Badge variant="warning">Wolne zmiany: {openCount}</Badge>}

            <div className="ml-auto flex flex-wrap items-center gap-2">
                {isDraft && (
                    <>
                        <NativeSelect value={templateId} onChange={(e) => setTemplateId(e.target.value)} className="h-9 w-44">
                            <option value="">Szablon...</option>
                            {templates.map((t) => (
                                <option key={t.id} value={t.id}>
                                    {t.name}
                                </option>
                            ))}
                        </NativeSelect>
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={!templateId}
                            onClick={() =>
                                window.confirm('Zastosować szablon? Obecne zmiany w szkicu zostaną zastąpione.') &&
                                post('manager.schedules.template', { template_id: templateId })
                            }
                        >
                            Zastosuj
                        </Button>
                        <Button size="sm" variant="outline" onClick={() => post('manager.schedules.auto')}>
                            <Sparkles /> Auto
                        </Button>
                    </>
                )}
                <Button size="sm" variant="outline" onClick={saveTemplate}>
                    <Save /> Zapisz jako szablon
                </Button>
                {isDraft && (
                    <Button
                        size="sm"
                        onClick={() => window.confirm('Opublikować grafik? Pracownicy od razu go zobaczą.') && post('manager.schedules.publish')}
                    >
                        <CalendarCheck /> Publikuj
                    </Button>
                )}
            </div>
        </div>
    );
}

function DayCard({
    day,
    shifts,
    positions,
    renderShift,
    onAdd,
}: {
    day: WeekDay;
    shifts: Shift[];
    positions: Position[];
    renderShift: (shift: Shift) => ReactNode;
    onAdd: () => void;
}) {
    return (
        <section className="bg-background rounded-md border">
            <header className="flex items-center justify-between border-b px-3 py-2">
                <h3 className={cn('text-sm font-semibold', day.isWeekend && 'text-primary')}>{formatDate(day.date)}</h3>
                <Button variant="ghost" size="icon-sm" onClick={onAdd} aria-label="Dodaj zmianę">
                    <Plus />
                </Button>
            </header>
            <div className="space-y-3 p-2">
                {shifts.length === 0 && <p className="text-muted-foreground px-1 py-2 text-xs">Brak zmian - dodaj ręcznie lub użyj szablonu.</p>}
                {positions.map((position) => {
                    const list = shifts.filter((s) => s.position?.id === position.id).sort((a, b) => a.start_time.localeCompare(b.start_time));
                    if (list.length === 0) return null;

                    return (
                        <div key={position.id} className="space-y-1">
                            <p className="text-muted-foreground px-1 text-[11px] font-medium tracking-wide uppercase">{position.name}</p>
                            {list.map(renderShift)}
                        </div>
                    );
                })}
            </div>
        </section>
    );
}

function DraggableEmployee({ employee, onClick }: { employee: BuilderEmployee; onClick: () => void }) {
    const { attributes, listeners, setNodeRef, transform, isDragging } = useDraggable({
        id: `employee-${employee.id}`,
        data: { employeeId: employee.id },
    });

    const ratio = employee.available_hours > 0 ? employee.assigned_hours / employee.available_hours : 0;

    return (
        <li
            ref={setNodeRef}
            style={transform ? { transform: `translate3d(${transform.x}px, ${transform.y}px, 0)` } : undefined}
            className={cn(
                'bg-background min-w-44 cursor-grab touch-none rounded-md border p-2 select-none lg:min-w-0',
                isDragging && 'border-primary relative z-50 shadow-md',
            )}
            onClick={onClick}
            {...listeners}
            {...attributes}
        >
            <div className="flex items-center gap-2">
                <GripVertical className="text-muted-foreground size-4 shrink-0" />
                <span className="flex-1 truncate text-sm font-medium">{employee.name}</span>
                {!employee.availability && <TriangleAlert className="text-warning size-4" aria-label="Brak dyspozycyjności" />}
            </div>
            <div className="mt-1.5 flex flex-wrap gap-1">
                {employee.positions.map((p) => (
                    <Badge key={p.id} variant="secondary" className="text-[10px]">
                        {p.name}
                    </Badge>
                ))}
            </div>
            <div className="text-muted-foreground mt-1.5 flex items-center gap-2 text-[11px] tabular-nums">
                <div className="bg-muted h-1 flex-1 overflow-hidden rounded-sm">
                    <div className="bg-primary h-full" style={{ width: `${Math.min(ratio, 1) * 100}%` }} />
                </div>
                {formatHours(employee.assigned_hours)} / {formatHours(employee.available_hours)}
            </div>
        </li>
    );
}

function DroppableShift({ shift, onClick }: { shift: Shift; onClick: () => void }) {
    const { setNodeRef, isOver } = useDroppable({ id: `shift-${shift.id}`, data: { shiftId: shift.id } });

    return (
        <button
            ref={setNodeRef}
            type="button"
            onClick={onClick}
            className={cn(
                'flex w-full items-center justify-between gap-2 rounded-sm border px-2 py-2 text-left text-xs transition-colors',
                shift.is_open ? 'border-open-foreground/20 bg-open text-open-foreground' : 'bg-background hover:bg-muted',
                isOver && 'border-primary bg-mine ring-primary ring-1',
            )}
        >
            <span className="min-w-0 flex-1 truncate font-medium">{shift.user?.name ?? 'Wolna zmiana'}</span>
            {shift.note && <ShiftNote note={shift.note} />}
            <span className="shrink-0 tabular-nums">
                {shift.start_time}-{shift.end_time}
            </span>
        </button>
    );
}

function AvailabilityDialog({ employee, days, onClose }: { employee: BuilderEmployee | null; days: string[]; onClose: () => void }) {
    return (
        <Dialog open={!!employee} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{employee?.name}</DialogTitle>
                    <DialogDescription>Dyspozycyjność na ten tydzień</DialogDescription>
                </DialogHeader>
                {employee?.availability ? (
                    <ul className="divide-y rounded-md border">
                        {days.map((date) => {
                            const day = employee.availability?.find((d) => d.date === date);

                            return (
                                <li key={date} className="flex items-center justify-between px-3 py-2 text-sm">
                                    <span>{formatDate(date, 'EEEE d.MM')}</span>
                                    {!day || day.unavailable ? (
                                        <span className="text-muted-foreground">nie może</span>
                                    ) : (
                                        <span className="font-medium tabular-nums">
                                            {day.start_time}-{day.end_time}
                                        </span>
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                ) : (
                    <p className="text-muted-foreground text-sm">Ta osoba nie wysłała dyspozycyjności.</p>
                )}
                {employee?.availability_note && <p className="bg-muted rounded-md p-3 text-sm italic">"{employee.availability_note}"</p>}
            </DialogContent>
        </Dialog>
    );
}

function ShiftDialog({
    shift,
    employees,
    onClose,
    onAssign,
}: {
    shift: Shift | null;
    employees: BuilderEmployee[];
    onClose: () => void;
    onAssign: (shift: Shift, employee: BuilderEmployee | null) => void;
}) {
    const eligible = employees.filter((e) => e.positions.some((p) => p.id === shift?.position?.id));

    return (
        <Dialog open={!!shift} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {shift?.position?.name} · {shift?.start_time}-{shift?.end_time}
                    </DialogTitle>
                    <DialogDescription>{shift && formatDate(shift.date)}</DialogDescription>
                </DialogHeader>
                <ul className="max-h-72 divide-y overflow-y-auto rounded-md border">
                    {eligible.map((employee) => (
                        <li key={employee.id}>
                            <button
                                type="button"
                                className={cn(
                                    'hover:bg-muted flex w-full items-center justify-between px-3 py-2 text-sm',
                                    shift?.user?.id === employee.id && 'bg-mine',
                                )}
                                onClick={() => shift && onAssign(shift, employee)}
                            >
                                {employee.name}
                                <span className="text-muted-foreground text-xs tabular-nums">{formatHours(employee.assigned_hours)}</span>
                            </button>
                        </li>
                    ))}
                </ul>
                <DialogFooter className="gap-2 sm:justify-between">
                    <Button
                        variant="outline"
                        onClick={() =>
                            shift &&
                            window.confirm('Usunąć tę zmianę z grafiku?') &&
                            router.delete(route('manager.shifts.destroy', shift.id), { preserveScroll: true, onSuccess: onClose })
                        }
                    >
                        <Trash2 /> Usuń zmianę
                    </Button>
                    {shift?.user && (
                        <Button variant="secondary" onClick={() => onAssign(shift, null)}>
                            <X /> Zostaw wolną
                        </Button>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function AddShiftDialog({
    scheduleId,
    date,
    positions,
    onClose,
}: {
    scheduleId: number;
    date: string | null;
    positions: Position[];
    onClose: () => void;
}) {
    const [form, setForm] = useState({ position_id: String(positions[0]?.id ?? ''), start_time: '16:00', end_time: '23:00', note: '' });

    const submit = () => {
        router.post(route('manager.shifts.store', scheduleId), { ...form, date }, { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Dialog open={!!date} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Nowa zmiana</DialogTitle>
                    <DialogDescription>{date && formatDate(date)}</DialogDescription>
                </DialogHeader>
                <div className="grid gap-3">
                    <div className="grid gap-2">
                        <Label htmlFor="position">Stanowisko</Label>
                        <NativeSelect id="position" value={form.position_id} onChange={(e) => setForm({ ...form, position_id: e.target.value })}>
                            {positions.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name}
                                </option>
                            ))}
                        </NativeSelect>
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                        <div className="grid gap-2">
                            <Label htmlFor="start">Od</Label>
                            <Input
                                id="start"
                                type="time"
                                step={900}
                                value={form.start_time}
                                onChange={(e) => setForm({ ...form, start_time: e.target.value })}
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="end">Do</Label>
                            <Input
                                id="end"
                                type="time"
                                step={900}
                                value={form.end_time}
                                onChange={(e) => setForm({ ...form, end_time: e.target.value })}
                            />
                        </div>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="note">Dopisek (opcjonalnie)</Label>
                        <Input
                            id="note"
                            maxLength={50}
                            placeholder="np. KIOSK, Plakaty, KK"
                            value={form.note}
                            onChange={(e) => setForm({ ...form, note: e.target.value })}
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button onClick={submit}>Dodaj zmianę</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
