<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RekomendasiEvaluasi: string implements HasColor, HasLabel
{
    case Lanjutkan = 'lanjutkan';
    case Perpanjang = 'perpanjang';
    case Revisi = 'revisi';
    case Hentikan = 'hentikan';

    public function label(): string
    {
        return match ($this) {
            self::Lanjutkan => 'Lanjutkan',
            self::Perpanjang => 'Perpanjang',
            self::Revisi => 'Revisi',
            self::Hentikan => 'Hentikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Lanjutkan => 'success',
            self::Perpanjang => 'info',
            self::Revisi => 'warning',
            self::Hentikan => 'danger',
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
