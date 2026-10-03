<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreAvailabilityRequest;
use App\Models\Availability;
use App\Models\Schedule;
use App\Support\CinemaWeek;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    public function edit(Request $request): Response
    {
        $week = CinemaWeek::next();

        $availability = Availability::where('user_id', $request->user()->id)
            ->whereDate('week_start', $week->toDateString())
            ->with('days')
            ->first();

        $days = $week->days()->map(function ($day) use ($availability) {
            $saved = $availability?->days->first(fn ($d) => $d->date->isSameDay($day));

            return [
                'date' => $day->toDateString(),
                'unavailable' => $saved?->unavailable ?? false,
                'start_time' => $saved?->start_time ? substr($saved->start_time, 0, 5) : '',
                'end_time' => $saved?->end_time ? substr($saved->end_time, 0, 5) : '',
            ];
        });

        return Inertia::render('employee/availability/edit', [
            'week' => $week->toArray(),
            'days' => $days,
            'note' => $availability?->note,
            'submittedAt' => $availability?->submitted_at?->toIso8601String(),
            'locked' => $this->isLocked($week),
        ]);
    }

    public function update(StoreAvailabilityRequest $request): RedirectResponse
    {
        $week = CinemaWeek::next();

        abort_if($this->isLocked($week), 422, 'Grafik na ten tydzień jest już opublikowany.');

        DB::transaction(function () use ($request, $week) {
            $availability = Availability::updateOrCreate(
                ['user_id' => $request->user()->id, 'week_start' => $week->toDateString()],
                ['note' => $request->validated('note'), 'submitted_at' => now()],
            );

            $availability->days()->delete();

            foreach ($request->validated('days') as $day) {
                $availability->days()->create([
                    'date' => $day['date'],
                    'unavailable' => $day['unavailable'],
                    'start_time' => $day['unavailable'] ? null : $day['start_time'],
                    'end_time' => $day['unavailable'] ? null : $day['end_time'],
                ]);
            }
        });

        return back()->with('success', 'Dyspozycyjność wysłana.');
    }

    private function isLocked(CinemaWeek $week): bool
    {
        return Schedule::published()->whereDate('week_start', $week->toDateString())->exists();
    }
}
