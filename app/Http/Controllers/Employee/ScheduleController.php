<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\PositionResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\ShiftResource;
use App\Models\Position;
use App\Models\Schedule;
use App\Support\CinemaWeek;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function index(Request $request): Response
    {
        $week = CinemaWeek::fromDate($request->date('week') ?? now());

        $schedule = Schedule::published()
            ->whereDate('week_start', $week->toDateString())
            ->with(['shifts.position', 'shifts.user', 'shifts.originalUser'])
            ->first();

        return Inertia::render('employee/schedule/index', [
            'week' => $week->toArray(),
            'prevWeek' => $week->addWeeks(-1)->toDateString(),
            'nextWeek' => $week->addWeeks(1)->toDateString(),
            'schedule' => $schedule ? new ScheduleResource($schedule) : null,
            'positions' => PositionResource::collection(Position::orderBy('sort_order')->get()),
        ]);
    }

    public function mine(Request $request): Response
    {
        $shifts = $request->user()->shifts()
            ->inPublishedSchedules()
            ->whereDate('date', '>=', CinemaWeek::current()->start)
            ->with('position')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('employee/schedule/mine', [
            'shifts' => ShiftResource::collection($shifts),
            'today' => today()->toDateString(),
        ]);
    }
}
