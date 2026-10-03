<?php

namespace App\Http\Controllers;

use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveController extends Controller
{
    public function index(): Response
    {
        $schedules = Schedule::published()
            ->withCount('shifts')
            ->orderByDesc('week_start')
            ->get();

        return Inertia::render('archive/index', [
            'schedules' => ScheduleResource::collection($schedules),
        ]);
    }

    public function show(Schedule $schedule): Response
    {
        $this->authorize('view', $schedule);

        $schedule->load(['shifts.position', 'shifts.user', 'shifts.originalUser']);

        return Inertia::render('archive/show', [
            'schedule' => new ScheduleResource($schedule),
        ]);
    }
}
