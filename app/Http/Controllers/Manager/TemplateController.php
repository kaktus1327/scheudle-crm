<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Resources\PositionResource;
use App\Models\Position;
use App\Models\ScheduleTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('manager/templates/index', [
            'templates' => ScheduleTemplate::withCount('shifts')->orderByDesc('is_system')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return $this->form(new ScheduleTemplate);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $template = DB::transaction(function () use ($request, $data) {
            $template = ScheduleTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'created_by' => $request->user()->id,
            ]);
            $template->shifts()->createMany($data['shifts']);

            return $template;
        });

        return to_route('manager.templates.edit', $template)->with('success', 'Szablon zapisany.');
    }

    public function edit(ScheduleTemplate $template): Response
    {
        return $this->form($template->load('shifts'));
    }

    public function update(Request $request, ScheduleTemplate $template): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($template, $data) {
            $template->update(['name' => $data['name'], 'description' => $data['description']]);
            $template->shifts()->delete();
            $template->shifts()->createMany($data['shifts']);
        });

        return back()->with('success', 'Szablon zaktualizowany.');
    }

    public function destroy(ScheduleTemplate $template): RedirectResponse
    {
        abort_if($template->is_system, 403, 'Szablonów systemowych nie można usuwać.');

        $template->delete();

        return to_route('manager.templates.index')->with('success', 'Szablon usunięty.');
    }

    private function form(ScheduleTemplate $template): Response
    {
        return Inertia::render('manager/templates/form', [
            'template' => $template->exists ? [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'is_system' => $template->is_system,
                'shifts' => $template->shifts->map(fn ($s) => [
                    'position_id' => $s->position_id,
                    'day_offset' => $s->day_offset,
                    'start_time' => substr($s->start_time, 0, 5),
                    'end_time' => substr($s->end_time, 0, 5),
                    'note' => $s->note ?? '',
                ]),
            ] : null,
            'positions' => PositionResource::collection(Position::orderBy('sort_order')->get()),
        ]);
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'shifts' => ['array'],
            'shifts.*.position_id' => ['required', 'exists:positions,id'],
            'shifts.*.day_offset' => ['required', 'integer', 'between:0,6'],
            'shifts.*.start_time' => ['required', 'date_format:H:i'],
            'shifts.*.end_time' => ['required', 'date_format:H:i'],
            'shifts.*.note' => ['nullable', 'string', 'max:50'],
        ]);

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'shifts' => $validated['shifts'] ?? [],
        ];
    }
}
