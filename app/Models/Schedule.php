<?php

namespace App\Models;

use App\Enums\ScheduleStatus;
use App\Support\CinemaWeek;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $fillable = ['week_start', 'status', 'schedule_template_id', 'created_by', 'published_at'];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'status' => ScheduleStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class)->orderBy('date')->orderBy('start_time');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ScheduleTemplate::class, 'schedule_template_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ScheduleStatus::Published);
    }

    public function isPublished(): bool
    {
        return $this->status === ScheduleStatus::Published;
    }

    public function week(): CinemaWeek
    {
        return CinemaWeek::fromDate($this->week_start);
    }
}
