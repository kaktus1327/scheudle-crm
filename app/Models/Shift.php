<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Shift extends Model
{
    protected $fillable = ['schedule_id', 'position_id', 'date', 'start_time', 'end_time', 'note', 'user_id', 'original_user_id'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function originalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_user_id')->withTrashed();
    }

    public function isOpen(): bool
    {
        return $this->user_id === null;
    }

    public function startsAt(): Carbon
    {
        return $this->date->copy()->setTimeFromTimeString($this->start_time);
    }

    public function endsAt(): Carbon
    {
        $end = $this->date->copy()->setTimeFromTimeString($this->end_time);

        return $end->lessThanOrEqualTo($this->startsAt()) ? $end->addDay() : $end;
    }

    public function durationInHours(): float
    {
        return round($this->startsAt()->diffInMinutes($this->endsAt()) / 60, 2);
    }

    public function scopeInPublishedSchedules(Builder $query): Builder
    {
        return $query->whereHas('schedule', fn (Builder $q) => $q->published());
    }
}
