<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = ['author_id', 'title', 'body', 'requires_acknowledgement'];

    protected function casts(): array
    {
        return ['requires_acknowledgement' => 'boolean'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withTrashed();
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_recipients')
            ->withPivot(['read_at', 'acknowledged_at'])
            ->withTrashed();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }
}
