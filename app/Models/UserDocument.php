<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocument extends Model
{
    protected $fillable = ['user_id', 'document_type_id', 'valid_until', 'note'];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'reminded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function daysLeft(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->valid_until, false);
    }

    public function isExpiringSoon(): bool
    {
        return $this->daysLeft() <= $this->type->remind_days_before;
    }

    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->whereDate('valid_until', '<=', now()->addDays($days));
    }
}
