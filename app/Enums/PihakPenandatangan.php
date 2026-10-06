<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PihakPenandatangan: string implements HasColor, HasLabel
{
    case Rektor = 'rektor';
    case Dekan = 'dekan';
    case WakilDekan = 'wakil_dekan';
    case Kaprodi = 'kaprodi';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Rektor => 'Rektor',
            self::Dekan => 'Dekan',
            self::WakilDekan => 'Wakil Dekan',
            self::Kaprodi => 'Kaprodi',
            self::Lainnya => 'Lainnya',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Rektor => 'danger',
            self::Dekan => 'warning',
            self::WakilDekan => 'info',
            self::Kaprodi => 'success',
            self::Lainnya => 'gray',
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
