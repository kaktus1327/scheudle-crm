<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class HoursCalculator
{

    public function shiftsBetween(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Shift::query()
            ->inPublishedSchedules()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get();
    }

    public function hoursInMonth(User $user, CarbonInterface $month): float
    {
        $start = CarbonImmutable::parse($month)->startOfMonth();

        return round($this->shiftsBetween($user, $start, $start->endOfMonth())->sum->durationInHours(), 2);
    }

    public function monthlyHistory(User $user, int $months = 6): array
    {
        $current = CarbonImmutable::now()->startOfMonth();

        return collect(range($months - 1, 0))
            ->map(function (int $back) use ($user, $current) {
                $month = $current->subMonths($back);

                return [
                    'month' => $month->format('Y-m'),
                    'label' => $month->locale('pl')->isoFormat('MMM'),
                    'hours' => $this->hoursInMonth($user, $month),
                ];
            })
            ->all();
    }

    public function weekdayWeekendSplit(User $user, CarbonInterface $month): array
    {
        $start = CarbonImmutable::parse($month)->startOfMonth();
        $shifts = $this->shiftsBetween($user, $start, $start->endOfMonth());

        $weekend = $shifts->filter(fn (Shift $s) => in_array($s->date->dayOfWeekIso, [5, 6, 7], true))->count();

        return ['weekday' => $shifts->count() - $weekend, 'weekend' => $weekend];
    }
}
