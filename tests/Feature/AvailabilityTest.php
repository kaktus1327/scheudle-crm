<?php

use App\Models\Availability;
use App\Support\CinemaWeek;

function availabilityPayload(CinemaWeek $week): array
{
    return [
        'note' => 'Sobota tylko do 22',
        'days' => $week->days()->map(fn ($day, $i) => [
            'date' => $day->toDateString(),
            'unavailable' => $i === 3,
            'start_time' => $i === 3 ? null : '12:00',
            'end_time' => $i === 3 ? null : '23:00',
        ])->all(),
    ];
}

test('employee submits availability for the next cinema week', function () {
    $employee = employee();

    $this->actingAs($employee)
        ->put('/availability', availabilityPayload(CinemaWeek::next()))
        ->assertSessionHasNoErrors();

    $availability = Availability::with('days')->sole();
    expect($availability->user_id)->toBe($employee->id)
        ->and($availability->week_start->isFriday())->toBeTrue()
        ->and($availability->days)->toHaveCount(7)
        ->and($availability->days->where('unavailable', true))->toHaveCount(1);
});

test('availability cannot be sent for the current week', function () {
    $this->actingAs(employee())
        ->put('/availability', availabilityPayload(CinemaWeek::current()))
        ->assertSessionHasErrors('days');

    expect(Availability::count())->toBe(0);
});

test('availability is locked once the schedule is published', function () {
    publishedSchedule(CinemaWeek::next());

    $this->actingAs(employee())
        ->put('/availability', availabilityPayload(CinemaWeek::next()))
        ->assertStatus(422);
});

test('hours are required unless the day is marked unavailable', function () {
    $payload = availabilityPayload(CinemaWeek::next());
    $payload['days'][0]['start_time'] = null;

    $this->actingAs(employee())
        ->put('/availability', $payload)
        ->assertSessionHasErrors('days.0.start_time');
});
