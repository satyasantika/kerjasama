<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Peran: string implements HasColor, HasLabel
{
    case SuperAdmin = 'super_admin';
    case AdminFakultas = 'admin_fakultas';
    case AdminProdi = 'admin_prodi';
    case Pimpinan = 'pimpinan';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::AdminFakultas => 'Admin Fakultas',
            self::AdminProdi => 'Admin Prodi',
            self::Pimpinan => 'Pimpinan',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    public function color(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::AdminFakultas => 'warning',
            self::AdminProdi => 'info',
            self::Pimpinan => 'success',
        };
    }

    public function getColor(): string
    {
        return $this->color();
    }
}
