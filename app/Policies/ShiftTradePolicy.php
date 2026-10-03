<?php

namespace App\Policies;

use App\Enums\TradeStatus;
use App\Models\ShiftTrade;
use App\Models\User;

class ShiftTradePolicy
{
    public function view(User $user, ShiftTrade $trade): bool
    {
        return $user->isManager()
            || $trade->status === TradeStatus::Open
            || in_array($user->id, [$trade->requester_id, $trade->target_user_id, $trade->claimer_id], true);
    }

    public function create(User $user): bool
    {
        return $user->isEmployee();
    }

    public function respond(User $user, ShiftTrade $trade): bool
    {
        return $trade->status === TradeStatus::PendingPeer && $trade->target_user_id === $user->id;
    }

    public function claim(User $user, ShiftTrade $trade): bool
    {
        return $user->isEmployee()
            && $trade->status === TradeStatus::Open
            && $trade->requester_id !== $user->id;
    }

    public function cancel(User $user, ShiftTrade $trade): bool
    {
        return $trade->requester_id === $user->id && ! $trade->status->isFinal();
    }

    public function decide(User $user, ShiftTrade $trade): bool
    {
        return $user->isManager() && $trade->status === TradeStatus::PendingManager;
    }
}
