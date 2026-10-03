<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Resources\PositionResource;
use App\Http\Resources\ScheduleResource;
use App\Models\Availability;
use App\Models\Position;
use App\Models\Schedule;
use App\Models\ScheduleTemplate;
use App\Models\User;
use App\Services\AutoScheduler;
use App\Services\ScheduleBuilder;
use App\Support\CinemaWeek;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleBuilder $builder) {}

    public function index(): Response
    {
        return Inertia::render('manager/schedules/index', [
            'schedules' => ScheduleResource::collection(
                Schedule::withCount('shifts')->orderByDesc('week_start')->limit(12)->get()
            ),
            'nextWeek' => CinemaWeek::next()->toArray(),
            'nextWeekExists' => Schedule::whereDate('week_start', CinemaWeek::next()->toDateString())->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['week_start' => ['required', 'date']]);

        $schedule = $this->builder->draftFor(CinemaWeek::fromDate($validated['week_start']), $request->user());

        return to_route('manager.schedules.edit', $schedule);
    }

    public function edit(Schedule $schedule): Response
    {
        $schedule->load(['shifts.position', 'shifts.user', 'template']);

        $availabilities = Availability::whereDate('week_start', $schedule->week_start)
            ->whereNotNull('submitted_at')
            ->with('days')
            ->get()
            ->keyBy('user_id');

        $employees = User::employees()->with('positions')->orderBy('name')->get()->map(function (User $u) use ($schedule, $availabilities) {
            $availability = $availabilities->get($u->id);

            return [
                'id' => $u->id,
                'name' => $u->name,
                'positions' => PositionResource::collection($u->positions),
                'assigned_hours' => round($schedule->shifts->where('user_id', $u->id)->sum->durationInHours(), 2),
                'available_hours' => $availability?->totalHours() ?? 0,
                'availability' => $availability?->days->map(fn ($d) => [
                    'date' => $d->date->toDateString(),
                    'unavailable' => $d->unavailable,
                    'start_time' => $d->start_time ? substr($d->start_time, 0, 5) : null,
                    'end_time' => $d->end_time ? substr($d->end_time, 0, 5) : null,
                ]),
                'availability_note' => $availability?->note,
            ];
        });

        return Inertia::render('manager/schedules/builder', [
            'schedule' => new ScheduleResource($schedule),
            'employees' => $employees,
            'positions' => PositionResource::collection(Position::orderBy('sort_order')->get()),
            'templates' => ScheduleTemplate::withCount('shifts')->orderByDesc('is_system')->orderBy('name')->get(['id', 'name', 'is_system']),
        ]);
    }

    public function applyTemplate(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate(['template_id' => ['required', 'exists:schedule_templates,id']]);

        $this->builder->applyTemplate($schedule, ScheduleTemplate::with('shifts')->findOrFail($validated['template_id']));

        return back()->with('success', 'Zastosowano szablon.');
    }

    public function saveAsTemplate(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:100']]);

        $this->builder->saveAsTemplate($schedule->load('shifts'), $validated['name'], $request->user());

        return back()->with('success', 'Zapisano szablon "'.$validated['name'].'".');
    }

    public function autoFill(Schedule $schedule, AutoScheduler $scheduler): RedirectResponse
    {
        abort_if($schedule->isPublished(), 422);

        $filled = $scheduler->fill($schedule);

        return back()->with('success', "Automatycznie obsadzono zmian: {$filled}.");
    }

    public function publish(Schedule $schedule): RedirectResponse
    {
        abort_if($schedule->isPublished(), 422);

        $this->builder->publish($schedule);

        return back()->with('success', 'Grafik opublikowany - pracownicy już go widzą.');
    }
}
