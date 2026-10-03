<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Availability extends Model
{
    protected $fillable = ['user_id', 'week_start', 'note', 'submitted_at'];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(AvailabilityDay::class)->orderBy('date');
    }

    public function totalHours(): float
    {
        return round($this->days->sum(fn (AvailabilityDay $day) => $day->hours()), 2);
    }
}
