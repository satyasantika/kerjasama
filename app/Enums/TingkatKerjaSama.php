<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TingkatKerjaSama: string implements HasColor, HasLabel
{
    case Lokal = 'lokal';
    case Nasional = 'nasional';
    case Internasional = 'internasional';

    public function label(): string
    {
        return match ($this) {
            self::Lokal => 'Lokal',
            self::Nasional => 'Nasional',
            self::Internasional => 'Internasional',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Lokal => 'gray',
            self::Nasional => 'info',
            self::Internasional => 'success',
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
