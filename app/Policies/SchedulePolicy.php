<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{

    public function view(User $user, Schedule $schedule): bool
    {
        return $user->isManager() || $schedule->isPublished();
    }

    public function manage(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $user->isManager();
    }
}
