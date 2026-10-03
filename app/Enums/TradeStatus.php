<?php

namespace App\Enums;

enum TradeStatus: string
{
    case PendingPeer = 'pending_peer';
    case Open = 'open';
    case PendingManager = 'pending_manager';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPeer => 'Czeka na pracownika',
            self::Open => 'Na giełdzie',
            self::PendingManager => 'Czeka na kierownika',
            self::Approved => 'Zaakceptowana',
            self::Rejected => 'Odrzucona',
            self::Cancelled => 'Anulowana',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected, self::Cancelled], true);
    }
}
