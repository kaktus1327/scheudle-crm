<?php

use App\Enums\ScheduleStatus;
use App\Models\Availability;
use App\Models\Position;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Support\CinemaWeek;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => test()->seed([PositionSeeder::class, DocumentTypeSeeder::class]))
    ->in('Feature');

function manager(array $attributes = []): User
{
    return User::factory()->manager()->create($attributes);
}

function employee(array $attributes = [], array $positions = ['obsluga']): User
{
    $user = User::factory()->create($attributes);
    $user->positions()->sync(Position::whereIn('slug', $positions)->pluck('id'));

    return $user;
}

function position(string $slug = 'obsluga'): Position
{
    return Position::where('slug', $slug)->firstOrFail();
}

function publishedSchedule(?CinemaWeek $week = null): Schedule
{
    $week ??= CinemaWeek::current();

    return Schedule::create([
        'week_start' => $week->toDateString(),
        'status' => ScheduleStatus::Published,
        'published_at' => now(),
    ]);
}

function shiftFor(?User $user, ?Schedule $schedule = null, ?string $date = null, string $start = '16:00', string $end = '22:00'): Shift
{
    $schedule ??= publishedSchedule();

    return Shift::create([
        'schedule_id' => $schedule->id,
        'position_id' => position()->id,
        'date' => $date ?? $schedule->week_start->copy()->addDays(6)->toDateString(),
        'start_time' => $start,
        'end_time' => $end,
        'user_id' => $user?->id,
        'original_user_id' => $user?->id,
    ]);
}

function availability(User $user, CinemaWeek $week, string $start = '10:00', string $end = '23:59'): Availability
{
    $availability = Availability::create([
        'user_id' => $user->id,
        'week_start' => $week->toDateString(),
        'submitted_at' => now(),
    ]);

    foreach ($week->days() as $day) {
        $availability->days()->create([
            'date' => $day->toDateString(),
            'unavailable' => false,
            'start_time' => $start,
            'end_time' => $end,
        ]);
    }

    return $availability;
}
