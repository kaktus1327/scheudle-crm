<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\ScheduleTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ScheduleTemplateSeeder extends Seeder
{
    private Collection $positions;

    public function run(): void
    {
        $this->positions = Position::pluck('id', 'slug');

        $this->template('Spokojny tydzień', 'Standardowy repertuar, mniej seansów w tygodniu', $this->sameEveryDay(
            weekend: [
                ['obsluga', '09:30', '16:00'],
                ['obsluga', '15:30', '22:00'],
                ['obsluga', '17:30', '00:30'],
                ['bar', '10:00', '17:00'],
                ['bar', '16:30', '23:00'],
                ['kawiarnia', '11:00', '19:00'],
            ],
            weekday: [
                ['obsluga', '15:00', '21:30'],
                ['obsluga', '17:00', '23:30'],
                ['bar', '15:30', '22:30'],
                ['kawiarnia', '15:00', '21:00'],
            ],
        ));

        $this->template('Intensywny tydzień', 'Premiery, ferie, maratony - więcej osób na każdej zmianie', $this->sameEveryDay(
            weekend: [
                ['obsluga', '09:00', '15:30'],
                ['obsluga', '09:30', '16:00'],
                ['obsluga', '15:00', '21:30'],
                ['obsluga', '16:00', '22:30'],
                ['obsluga', '18:00', '01:00'],
                ['kasa', '09:00', '16:00'],
                ['bar', '09:30', '16:30'],
                ['bar', '15:30', '22:30'],
                ['bar', '17:00', '00:30'],
                ['kawiarnia', '10:00', '17:00'],
                ['kawiarnia', '16:00', '22:00'],
            ],
            weekday: [
                ['obsluga', '11:00', '17:30'],
                ['obsluga', '15:00', '21:30'],
                ['obsluga', '17:00', '23:30'],
                ['bar', '11:30', '18:30'],
                ['bar', '16:30', '23:30'],
                ['kawiarnia', '12:00', '20:00'],
            ],
        ));

        $this->template('Kino Kobiet', 'Tydzień z porannym Kinem Kobiet w środę', [
            0 => [
                ['obsluga', '08:15', '16:00'], ['obsluga', '14:00', '21:00'], ['obsluga', '15:00', '22:30'], ['obsluga', '16:00', '00:10'],
                ['bar', '08:15', '16:30'], ['bar', '08:15', '16:30'], ['bar', '15:00', '21:00'], ['bar', '15:00', '21:00', 'KIOSK'],
                ['bar', '16:30', '23:00'], ['bar', '16:30', '23:00'],
                ['kawiarnia', '08:15', '15:30'], ['kawiarnia', '15:30', '23:00'],
            ],
            1 => [
                ['obsluga', '09:15', '16:00'], ['obsluga', '14:00', '21:00'], ['obsluga', '15:00', '22:30'], ['obsluga', '16:00', '23:30'],
                ['bar', '08:45', '15:30'], ['bar', '09:15', '15:30'], ['bar', '13:00', '21:00', 'KIOSK'], ['bar', '15:00', '21:30'],
                ['bar', '15:30', '22:00'], ['bar', '15:30', '22:00'],
                ['kawiarnia', '09:15', '15:30'], ['kawiarnia', '15:30', '22:00'],
            ],
            2 => [
                ['obsluga', '09:15', '16:00'], ['obsluga', '13:00', '21:00'], ['obsluga', '15:00', '22:00'], ['obsluga', '16:00', '23:30'],
                ['bar', '09:00', '15:30'], ['bar', '09:15', '15:30'], ['bar', '10:30', '19:30', 'KIOSK'], ['bar', '12:15', '21:30'],
                ['bar', '15:30', '21:50'], ['bar', '15:30', '21:50'],
                ['kawiarnia', '09:15', '15:30'], ['kawiarnia', '15:30', '21:50'],
            ],
            3 => [
                ['obsluga', '08:15', '16:00'], ['obsluga', '09:00', '13:00'], ['obsluga', '15:30', '22:30'], ['obsluga', '16:00', '23:45'],
                ['kasa', '08:15', '13:00'],
                ['bar', '08:15', '17:00'], ['bar', '08:15', '15:30'], ['bar', '08:15', '13:00'], ['bar', '08:15', '13:00'],
                ['bar', '17:00', '22:05'], ['bar', '15:30', '22:05'],
                ['kawiarnia', '08:15', '13:00'],
            ],
            4 => [
                ['obsluga', '08:15', '16:00'], ['obsluga', '15:00', '22:30'], ['obsluga', '16:00', '23:30'],
                ['bar', '08:15', '15:30'], ['bar', '08:15', '14:00'], ['bar', '14:00', '21:00', 'KIOSK'],
                ['bar', '15:30', '22:00'], ['bar', '15:30', '22:00'],
                ['kawiarnia', '08:15', '15:30'], ['kawiarnia', '15:30', '22:00'],
            ],
            5 => [
                ['obsluga', '07:45', '14:00'], ['obsluga', '08:00', '15:00'], ['obsluga', '08:15', '15:00'], ['obsluga', '08:15', '19:00', 'Zdjęcia'],
                ['obsluga', '15:30', '22:30'], ['obsluga', '16:00', '23:50'],
                ['kasa', '07:45', '13:00'], ['kasa', '07:45', '19:00', 'KK'],
                ['bar', '07:45', '15:30'], ['bar', '07:45', '19:00', 'KK'], ['bar', '07:45', '15:30'], ['bar', '07:45', '14:00'],
                ['bar', '15:00', '21:35', 'KK'], ['bar', '15:30', '21:35'], ['bar', '15:30', '21:35'],
                ['kawiarnia', '07:45', '15:30'], ['kawiarnia', '15:30', '21:35'],
            ],
            6 => [
                ['obsluga', '08:15', '16:00'], ['obsluga', '15:30', '22:30', 'Plakaty'], ['obsluga', '16:00', '00:30', 'Plakaty'],
                ['bar', '07:00', '16:00', 'Inwentaryzacja'], ['bar', '07:00', '16:30', 'Inwentaryzacja'], ['bar', '07:00', '14:00', 'Inwentaryzacja'],
                ['bar', '16:00', '21:50'], ['bar', '16:30', '21:50'],
                ['kawiarnia', '07:00', '13:00', 'Inwentaryzacja'],
            ],
        ]);
    }

    private function sameEveryDay(array $weekend, array $weekday): array
    {
        return collect(range(0, 6))->mapWithKeys(fn (int $offset) => [$offset => $offset <= 2 ? $weekend : $weekday])->all();
    }

    private function template(string $name, string $description, array $days): void
    {
        $template = ScheduleTemplate::updateOrCreate(
            ['name' => $name, 'is_system' => true],
            ['description' => $description],
        );

        $template->shifts()->delete();

        foreach ($days as $offset => $shifts) {
            foreach ($shifts as $shift) {
                $template->shifts()->create([
                    'position_id' => $this->positions[$shift[0]],
                    'day_offset' => $offset,
                    'start_time' => $shift[1],
                    'end_time' => $shift[2],
                    'note' => $shift[3] ?? null,
                ]);
            }
        }
    }
}
