<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AutoScheduler
{
    public function __construct(private readonly AvailabilityChecker $checker) {}

    public function fill(Schedule $schedule): int
    {
        $employees = User::employees()->with('positions')->get();

        $availableHours = Availability::query()
            ->whereDate('week_start', $schedule->week_start)
            ->whereNotNull('submitted_at')
            ->with('days')
            ->get()
            ->mapWithKeys(fn (Availability $a) => [$a->user_id => $a->totalHours()]);

        $shifts = $schedule->shifts()->get();

        $assignedHours = $employees->mapWithKeys(fn (User $u) => [
            $u->id => $shifts->where('user_id', $u->id)->sum->durationInHours(),
        ]);

        $filled = 0;

        DB::transaction(function () use ($shifts, $employees, $availableHours, &$assignedHours, &$filled) {
            foreach ($shifts->whereNull('user_id') as $shift) {
                $candidate = $employees
                    ->filter(fn (User $u) => $u->canWorkAt($shift->position_id))
                    ->filter(fn (User $u) => ($availableHours[$u->id] ?? 0) > 0)
                    ->reject(fn (User $u) => $this->hasShiftThatDay($shifts, $u, $shift))
                    ->filter(fn (User $u) => $this->checker->fits($u, $shift))
                    ->sortBy(fn (User $u) => $assignedHours[$u->id] / $availableHours[$u->id])
                    ->first();

                if (! $candidate) {
                    continue;
                }

                $shift->update(['user_id' => $candidate->id]);
                $assignedHours[$candidate->id] += $shift->durationInHours();
                $filled++;
            }
        });

        return $filled;
    }

    private function hasShiftThatDay(Collection $shifts, User $user, Shift $shift): bool
    {
        return $shifts->contains(fn (Shift $s) => $s->user_id === $user->id && $s->date->isSameDay($shift->date));
    }
}
