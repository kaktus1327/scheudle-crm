<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{

    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'hourly_rate',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'hourly_rate' => 'decimal:2',
            'must_change_password' => 'boolean',
        ];
    }

    public function isManager(): bool
    {
        return $this->role === Role::Manager;
    }

    public function isEmployee(): bool
    {
        return $this->role === Role::Employee;
    }

    public function scopeEmployees(Builder $query): Builder
    {
        return $query->where('role', Role::Employee);
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(UserDocument::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function receivedMessages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_recipients')
            ->withPivot(['read_at', 'acknowledged_at']);
    }

    public function canWorkAt(Position|int $position): bool
    {
        $id = $position instanceof Position ? $position->id : $position;

        return $this->positions->contains('id', $id);
    }
}
