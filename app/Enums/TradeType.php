<?php

namespace App\Enums;

enum TradeType: string
{

    case Swap = 'swap';

    case Giveaway = 'giveaway';

    case OpenOffer = 'open_offer';

    case OpenShiftClaim = 'open_shift_claim';

    public function label(): string
    {
        return match ($this) {
            self::Swap => 'Zamiana',
            self::Giveaway => 'Oddanie',
            self::OpenOffer => 'Giełda',
            self::OpenShiftClaim => 'Pusta zmiana',
        };
    }

    public function requiresPeerAcceptance(): bool
    {
        return in_array($this, [self::Swap, self::Giveaway], true);
    }
}
