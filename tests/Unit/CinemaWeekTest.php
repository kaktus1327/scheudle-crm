<?php

use App\Support\CinemaWeek;

test('cinema week starts on friday and ends on thursday', function (string $date, string $expectedStart) {
    $week = CinemaWeek::fromDate($date);

    expect($week->start->toDateString())->toBe($expectedStart)
        ->and($week->end()->isThursday())->toBeTrue()
        ->and($week->days())->toHaveCount(7);
})->with([
    'piątek' => ['2026-09-25', '2026-09-25'],
    'niedziela' => ['2026-09-27', '2026-09-25'],
    'czwartek' => ['2026-10-01', '2026-09-25'],
    'kolejny piątek' => ['2026-10-02', '2026-10-02'],
]);
