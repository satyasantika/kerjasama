<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisMitra: string implements HasColor, HasLabel
{
    case PerguruanTinggi = 'perguruan_tinggi';
    case Sekolah = 'sekolah';
    case Pemerintah = 'pemerintah';
    case Dudi = 'dudi';
    case OrganisasiProfesi = 'organisasi_profesi';
    case LembagaLain = 'lembaga_lain';

    public function label(): string
    {
        return match ($this) {
            self::PerguruanTinggi => 'Perguruan Tinggi',
            self::Sekolah => 'Sekolah',
            self::Pemerintah => 'Pemerintah',
            self::Dudi => 'Dunia Usaha/Industri',
            self::OrganisasiProfesi => 'Organisasi Profesi',
            self::LembagaLain => 'Lembaga Lain',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PerguruanTinggi => 'info',
            self::Sekolah => 'success',
            self::Pemerintah => 'warning',
            self::Dudi => 'primary',
            self::OrganisasiProfesi => 'danger',
            self::LembagaLain => 'gray',
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
