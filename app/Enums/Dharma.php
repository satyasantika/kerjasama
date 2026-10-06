<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Dharma: string implements HasColor, HasLabel
{
    case Pendidikan = 'pendidikan';
    case Penelitian = 'penelitian';
    case Pkm = 'pkm';

    public function label(): string
    {
        return match ($this) {
            self::Pendidikan => 'Pendidikan',
            self::Penelitian => 'Penelitian',
            self::Pkm => 'Pengabdian kepada Masyarakat',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendidikan => 'info',
            self::Penelitian => 'success',
            self::Pkm => 'warning',
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
