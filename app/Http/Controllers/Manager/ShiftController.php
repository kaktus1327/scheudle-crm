<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\AssignShiftRequest;
use App\Http\Requests\Manager\ShiftRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Services\AvailabilityChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    public function store(ShiftRequest $request, Schedule $schedule): RedirectResponse
    {
        $this->ensureDateInWeek($schedule, $request->validated('date'));

        $schedule->shifts()->create($request->validated());

        return back();
    }

    public function update(ShiftRequest $request, Shift $shift): RedirectResponse
    {
        $this->ensureDateInWeek($shift->schedule, $request->validated('date'));

        $shift->update($request->validated());

        return back();
    }

    public function assign(AssignShiftRequest $request, Shift $shift, AvailabilityChecker $checker): RedirectResponse
    {
        $userId = $request->validated('user_id');

        if ($userId) {
            $user = User::with('positions')->findOrFail($userId);

            if (! $user->canWorkAt($shift->position_id)) {
                throw ValidationException::withMessages(['position' => "{$user->name} nie ma uprawnień do stanowiska {$shift->position->name}."]);
            }

            if (! $request->boolean('force') && ! $checker->fits($user, $shift)) {
                throw ValidationException::withMessages(['availability' => "{$user->name} nie podał(a) dyspozycyjności na te godziny."]);
            }
        }

        $shift->update(['user_id' => $userId]);

        return back();
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $shift->delete();

        return back();
    }

    private function ensureDateInWeek(Schedule $schedule, string $date): void
    {
        if (! $schedule->week()->contains(now()->parse($date))) {
            throw ValidationException::withMessages(['date' => 'Data musi mieścić się w tygodniu grafiku.']);
        }
    }
}
