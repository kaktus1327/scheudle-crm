<?php

use App\Enums\ScheduleStatus;
use App\Models\Schedule;
use App\Models\ScheduleTemplate;
use App\Support\CinemaWeek;
use Database\Seeders\ScheduleTemplateSeeder;

beforeEach(function () {
    $this->seed(ScheduleTemplateSeeder::class);
    $this->manager = manager();
    $this->week = CinemaWeek::next();
});

test('manager creates a draft and applies a template', function () {
    $this->actingAs($this->manager)->post('/manager/schedules', ['week_start' => $this->week->toDateString()]);
    $schedule = Schedule::sole();

    $this->post("/manager/schedules/{$schedule->id}/template", ['template_id' => 1])->assertSessionHasNoErrors();

    expect($schedule->status)->toBe(ScheduleStatus::Draft)
        ->and($schedule->shifts()->count())->toBeGreaterThan(20)
        ->and($schedule->shifts()->whereNotNull('user_id')->count())->toBe(0);
});

test('women cinema template keeps notes and the ticket office', function () {
    $template = ScheduleTemplate::where('name', 'Kino Kobiet')->sole();
    $schedule = Schedule::create(['week_start' => $this->week->toDateString(), 'status' => ScheduleStatus::Draft]);

    $this->actingAs($this->manager)->post("/manager/schedules/{$schedule->id}/template", ['template_id' => $template->id]);

    $wednesday = $this->week->dayForOffset(5)->toDateString();
    expect($schedule->shifts()->count())->toBe(84)
        ->and($schedule->shifts()->whereDate('date', $wednesday)->where('note', 'KK')->count())->toBe(3)
        ->and($schedule->shifts()->where('position_id', position('kasa')->id)->count())->toBe(3);
});

test('assigning outside availability needs explicit confirmation', function () {
    $employee = employee();
    $schedule = Schedule::create(['week_start' => $this->week->toDateString(), 'status' => ScheduleStatus::Draft]);
    $shift = shiftFor(null, $schedule, start: '08:00', end: '14:00');
    availability($employee, $this->week, '15:00', '23:00');

    $this->actingAs($this->manager)
        ->put("/manager/shifts/{$shift->id}/assign", ['user_id' => $employee->id])
        ->assertSessionHasErrors('availability');
    expect($shift->fresh()->user_id)->toBeNull();

    $this->put("/manager/shifts/{$shift->id}/assign", ['user_id' => $employee->id, 'force' => true])->assertSessionHasNoErrors();
    expect($shift->fresh()->user_id)->toBe($employee->id);
});

test('employee without the position cannot be assigned even with force', function () {
    $barista = employee(positions: ['kawiarnia']);
    $schedule = Schedule::create(['week_start' => $this->week->toDateString(), 'status' => ScheduleStatus::Draft]);
    $shift = shiftFor(null, $schedule);

    $this->actingAs($this->manager)
        ->put("/manager/shifts/{$shift->id}/assign", ['user_id' => $barista->id, 'force' => true])
        ->assertSessionHasErrors('position');
});

test('auto scheduler fills shifts only within availability and spreads hours', function () {
    $a = employee();
    $b = employee();
    availability($a, $this->week);
    availability($b, $this->week);

    $schedule = Schedule::create(['week_start' => $this->week->toDateString(), 'status' => ScheduleStatus::Draft]);
    foreach ($this->week->days() as $day) {
        shiftFor(null, $schedule, $day->toDateString());
    }

    $this->actingAs($this->manager)->post("/manager/schedules/{$schedule->id}/auto");

    $shifts = $schedule->shifts()->get();
    expect($shifts->whereNull('user_id'))->toHaveCount(0)
        ->and(abs($shifts->where('user_id', $a->id)->count() - $shifts->where('user_id', $b->id)->count()))->toBeLessThanOrEqual(1);
});

test('publishing freezes the original schedule', function () {
    $employee = employee();
    $schedule = Schedule::create(['week_start' => $this->week->toDateString(), 'status' => ScheduleStatus::Draft]);
    $shift = shiftFor(null, $schedule);
    $shift->update(['user_id' => $employee->id, 'original_user_id' => null]);

    $this->actingAs($this->manager)->post("/manager/schedules/{$schedule->id}/publish");

    expect($schedule->fresh()->isPublished())->toBeTrue()
        ->and($shift->fresh()->original_user_id)->toBe($employee->id);
});
