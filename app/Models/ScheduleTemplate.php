<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleTemplate extends Model
{
    protected $fillable = ['name', 'description', 'is_system', 'created_by'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(TemplateShift::class)->orderBy('day_offset')->orderBy('start_time');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
