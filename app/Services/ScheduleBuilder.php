<?php

namespace App\Services;

use App\Enums\ScheduleStatus;
use App\Models\Schedule;
use App\Models\ScheduleTemplate;
use App\Models\TemplateShift;
use App\Models\User;
use App\Support\CinemaWeek;
use Illuminate\Support\Facades\DB;

class ScheduleBuilder
{
    public function draftFor(CinemaWeek $week, User $manager): Schedule
    {
        return Schedule::firstOrCreate(
            ['week_start' => $week->toDateString()],
            ['status' => ScheduleStatus::Draft, 'created_by' => $manager->id],
        );
    }

    public function applyTemplate(Schedule $schedule, ScheduleTemplate $template): void
    {
        abort_if($schedule->isPublished(), 422, 'Opublikowanego grafiku nie można nadpisać szablonem.');

        DB::transaction(function () use ($schedule, $template) {
            $schedule->shifts()->delete();

            $week = $schedule->week();

            $template->shifts->each(fn (TemplateShift $ts) => $schedule->shifts()->create([
                'position_id' => $ts->position_id,
                'date' => $week->dayForOffset($ts->day_offset)->toDateString(),
                'start_time' => $ts->start_time,
                'end_time' => $ts->end_time,
                'note' => $ts->note,
            ]));

            $schedule->update(['schedule_template_id' => $template->id]);
        });
    }

    public function saveAsTemplate(Schedule $schedule, string $name, User $manager): ScheduleTemplate
    {
        return DB::transaction(function () use ($schedule, $name, $manager) {
            $template = ScheduleTemplate::create(['name' => $name, 'created_by' => $manager->id]);

            foreach ($schedule->shifts as $shift) {
                $template->shifts()->create([
                    'position_id' => $shift->position_id,
                    'day_offset' => (int) $schedule->week_start->diffInDays($shift->date),
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                    'note' => $shift->note,
                ]);
            }

            return $template;
        });
    }

    public function publish(Schedule $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            $schedule->shifts()->update(['original_user_id' => DB::raw('user_id')]);
            $schedule->update(['status' => ScheduleStatus::Published, 'published_at' => now()]);
        });
    }
}
