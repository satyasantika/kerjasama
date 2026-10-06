<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusManual: string implements HasColor, HasLabel
{
    case Dibatalkan = 'dibatalkan';
    case Dihentikan = 'dihentikan';

    public function label(): string
    {
        return match ($this) {
            self::Dibatalkan => 'Dibatalkan',
            self::Dihentikan => 'Dihentikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Dibatalkan => 'danger',
            self::Dihentikan => 'gray',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return $this->color();
    }
}
