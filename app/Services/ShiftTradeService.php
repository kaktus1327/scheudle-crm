<?php

namespace App\Services;

use App\Enums\TradeStatus;
use App\Enums\TradeType;
use App\Models\Shift;
use App\Models\ShiftTrade;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShiftTradeService
{
    public function __construct(private readonly AvailabilityChecker $checker) {}

    public function propose(User $requester, Shift $offered, TradeType $type, ?User $target = null, ?Shift $requested = null, ?string $note = null): ShiftTrade
    {
        if ($type !== TradeType::OpenShiftClaim && $offered->user_id !== $requester->id) {
            throw ValidationException::withMessages(['offered_shift_id' => 'To nie jest Twoja zmiana.']);
        }

        if ($type === TradeType::Swap && (! $requested || ! $target || $requested->user_id !== $target->id)) {
            throw ValidationException::withMessages(['requested_shift_id' => 'Wybierz zmianę tej osoby do zamiany.']);
        }

        $status = match ($type) {
            TradeType::Swap, TradeType::Giveaway => TradeStatus::PendingPeer,
            TradeType::OpenOffer => TradeStatus::Open,
            TradeType::OpenShiftClaim => TradeStatus::PendingManager,
        };

        $trade = ShiftTrade::create([
            'type' => $type,
            'status' => $status,
            'requester_id' => $requester->id,
            'target_user_id' => $target?->id,
            'claimer_id' => $type === TradeType::OpenShiftClaim ? $requester->id : null,
            'offered_shift_id' => $offered->id,
            'requested_shift_id' => $requested?->id,
            'note' => $note,
        ]);

        return $this->refreshConflictFlag($trade);
    }

    public function respondAsPeer(ShiftTrade $trade, bool $accept): ShiftTrade
    {
        $trade->forceFill([
            'status' => $accept ? TradeStatus::PendingManager : TradeStatus::Rejected,
            'peer_responded_at' => now(),
        ])->save();

        return $this->refreshConflictFlag($trade);
    }

    public function claim(ShiftTrade $trade, User $claimer): ShiftTrade
    {
        return DB::transaction(function () use ($trade, $claimer) {
            $locked = ShiftTrade::whereKey($trade->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== TradeStatus::Open) {
                throw ValidationException::withMessages(['trade' => 'Ktoś był szybszy - ta zmiana jest już zajęta.']);
            }

            $locked->forceFill(['claimer_id' => $claimer->id, 'status' => TradeStatus::PendingManager])->save();

            return $this->refreshConflictFlag($locked);
        });
    }

    public function decide(ShiftTrade $trade, User $manager, bool $approve): ShiftTrade
    {
        return DB::transaction(function () use ($trade, $manager, $approve) {
            if ($approve) {
                $this->applyToSchedule($trade);
            }

            $trade->forceFill([
                'status' => $approve ? TradeStatus::Approved : TradeStatus::Rejected,
                'decided_by' => $manager->id,
                'decided_at' => now(),
            ])->save();

            return $trade;
        });
    }

    private function applyToSchedule(ShiftTrade $trade): void
    {
        $receiver = $trade->receiver();
        $offered = $trade->offeredShift;

        if ($trade->type === TradeType::Swap) {
            $trade->requestedShift->update(['user_id' => $trade->requester_id]);
        }

        $offered->update(['user_id' => $receiver->id]);
    }

    private function refreshConflictFlag(ShiftTrade $trade): ShiftTrade
    {
        $receiver = $trade->receiver();

        $conflict = $receiver && ! $this->checker->fits($receiver, $trade->offeredShift);

        if ($trade->type === TradeType::Swap && $trade->requestedShift) {
            $conflict = $conflict || ! $this->checker->fits($trade->requester, $trade->requestedShift);
        }

        $trade->forceFill(['availability_conflict' => $conflict])->save();

        return $trade;
    }
}
