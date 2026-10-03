<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonInterface;

class PayrollCalculator
{
    public function __construct(private readonly HoursCalculator $hours) {}

    public function grossForMonth(User $user, CarbonInterface $month): float
    {
        return round($this->hours->hoursInMonth($user, $month) * (float) $user->hourly_rate, 2);
    }
}
