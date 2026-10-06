<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BidangBentuk: string implements HasColor, HasLabel
{
    case Akademik = 'akademik';
    case NonAkademik = 'non_akademik';

    public function label(): string
    {
        return match ($this) {
            self::Akademik => 'Akademik',
            self::NonAkademik => 'Non-akademik',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Akademik => 'info',
            self::NonAkademik => 'warning',
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
