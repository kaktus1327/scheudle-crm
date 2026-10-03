<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\AvailabilityDay;
use App\Models\Shift;
use App\Models\User;
use App\Support\CinemaWeek;

class AvailabilityChecker
{

    private array $cache = [];

    public function fits(User $user, Shift $shift): bool
    {
        $day = $this->dayFor($user, $shift);

        if (! $day || $day->unavailable || ! $day->startsAt() || ! $day->endsAt()) {
            return false;
        }

        return $shift->startsAt()->greaterThanOrEqualTo($day->startsAt())
            && $shift->endsAt()->lessThanOrEqualTo($day->endsAt());
    }

    public function dayFor(User $user, Shift $shift): ?AvailabilityDay
    {
        $week = CinemaWeek::fromDate($shift->date)->toDateString();
        $key = "{$user->id}:{$week}";

        if (! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = Availability::query()
                ->where('user_id', $user->id)
                ->whereDate('week_start', $week)
                ->whereNotNull('submitted_at')
                ->with('days')
                ->first();
        }

        $availability = $this->cache[$key];

        return $availability?->days->first(fn (AvailabilityDay $d) => $d->date->isSameDay($shift->date));
    }
}
