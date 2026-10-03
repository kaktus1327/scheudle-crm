import InputError from '@/components/input-error';
import { Panel } from '@/components/panel';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type Person } from '@/types';
import { useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

export default function CreateMessage({ employees }: { employees: Person[] }) {
    const { data, setData, post, processing, errors, progress } = useForm({
        title: '',
        body: '',
        requires_acknowledgement: false as boolean,
        recipient_ids: [] as number[],
        attachments: [] as File[],
    });

    const toggleRecipient = (id: number) =>
        setData('recipient_ids', data.recipient_ids.includes(id) ? data.recipient_ids.filter((r) => r !== id) : [...data.recipient_ids, id]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('manager.messages.store'), { forceFormData: true });
    };

    return (
        <AppLayout title="Nowa wiadomość">
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4">
                <Panel>
                    <div className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="title">Temat</Label>
                            <Input id="title" value={data.title} onChange={(e) => setData('title', e.target.value)} required maxLength={150} />
                            <InputError message={errors.title} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="body">Treść</Label>
                            <Textarea id="body" value={data.body} onChange={(e) => setData('body', e.target.value)} className="min-h-40" required />
                            <InputError message={errors.body} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="attachments">Załączniki (PDF, obrazy, dokumenty, max 10 MB)</Label>
                            <Input
                                id="attachments"
                                type="file"
                                multiple
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                onChange={(e) => setData('attachments', Array.from(e.target.files ?? []))}
                            />
                            {progress && <progress value={progress.percentage} max="100" className="w-full" />}
                            <InputError message={errors.attachments ?? (errors as Record<string, string>)['attachments.0']} />
                        </div>
                        <label className="bg-secondary/50 flex items-start gap-3 rounded-md border p-3 text-sm">
                            <Checkbox
                                checked={data.requires_acknowledgement}
                                onCheckedChange={(checked) => setData('requires_acknowledgement', checked === true)}
                                className="mt-0.5"
                            />
                            <span>
                                <span className="font-medium">Wymagaj potwierdzenia zapoznania się</span>
                                <span className="text-muted-foreground block text-xs">Zobaczysz, kto kliknął "Zapoznałem się", a kto nie.</span>
                            </span>
                        </label>
                    </div>
                </Panel>

                <Panel
                    title="Odbiorcy"
                    description={
                        data.recipient_ids.length ? `Wybrano: ${data.recipient_ids.length}` : 'Nikogo nie zaznaczono - wiadomość trafi do wszystkich'
                    }
                >
                    <div className="flex flex-wrap gap-2">
                        {employees.map((employee) => (
                            <button
                                key={employee.id}
                                type="button"
                                onClick={() => toggleRecipient(employee.id)}
                                className={cn(
                                    'h-9 rounded-md border px-3 text-sm',
                                    data.recipient_ids.includes(employee.id) ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted',
                                )}
                            >
                                {employee.name}
                            </button>
                        ))}
                    </div>
                </Panel>

                <Button type="submit" size="lg" className="w-full" disabled={processing}>
                    Wyślij wiadomość
                </Button>
            </form>
        </AppLayout>
    );
}
