<?php

namespace App\Enums;

enum Role: string
{
    case Manager = 'manager';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::Manager => 'Kierownik',
            self::Employee => 'Pracownik',
        };
    }
}
