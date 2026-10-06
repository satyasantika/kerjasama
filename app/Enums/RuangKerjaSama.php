<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RuangKerjaSama: string implements HasColor, HasLabel
{
    case AntarPt = 'antar_pt';
    case DudiPihakLain = 'dudi_pihak_lain';

    public function label(): string
    {
        return match ($this) {
            self::AntarPt => 'Antar-PT',
            self::DudiPihakLain => 'Dunia Usaha/Pihak Lain',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AntarPt => 'info',
            self::DudiPihakLain => 'success',
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
