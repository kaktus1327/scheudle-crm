<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AvailabilityDay extends Model
{
    protected $fillable = ['availability_id', 'date', 'unavailable', 'start_time', 'end_time'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'unavailable' => 'boolean',
        ];
    }

    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class);
    }

    public function startsAt(): ?Carbon
    {
        return $this->unavailable || ! $this->start_time
            ? null
            : $this->date->copy()->setTimeFromTimeString($this->start_time);
    }

    public function endsAt(): ?Carbon
    {
        if ($this->unavailable || ! $this->end_time) {
            return null;
        }

        $end = $this->date->copy()->setTimeFromTimeString($this->end_time);

        return $end->lessThanOrEqualTo($this->startsAt()) ? $end->addDay() : $end;
    }

    public function hours(): float
    {
        $start = $this->startsAt();
        $end = $this->endsAt();

        return $start && $end ? round($start->diffInMinutes($end) / 60, 2) : 0.0;
    }
}
