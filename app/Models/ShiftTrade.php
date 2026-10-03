<?php

namespace App\Models;

use App\Enums\TradeStatus;
use App\Enums\TradeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftTrade extends Model
{
    protected $fillable = [
        'type',
        'status',
        'requester_id',
        'target_user_id',
        'claimer_id',
        'offered_shift_id',
        'requested_shift_id',
        'availability_conflict',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'type' => TradeType::class,
            'status' => TradeStatus::class,
            'availability_conflict' => 'boolean',
            'peer_responded_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id')->withTrashed();
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id')->withTrashed();
    }

    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimer_id')->withTrashed();
    }

    public function offeredShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'offered_shift_id');
    }

    public function requestedShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'requested_shift_id');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by')->withTrashed();
    }

    public function receiver(): ?User
    {
        return match ($this->type) {
            TradeType::Swap, TradeType::Giveaway => $this->targetUser,
            TradeType::OpenOffer, TradeType::OpenShiftClaim => $this->claimer,
        };
    }

    public function scopeInvolving(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('requester_id', $user->id)
            ->orWhere('target_user_id', $user->id)
            ->orWhere('claimer_id', $user->id));
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [TradeStatus::PendingPeer, TradeStatus::Open, TradeStatus::PendingManager]);
    }
}
