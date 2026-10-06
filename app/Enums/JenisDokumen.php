<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisDokumen: string implements HasColor, HasLabel
{
    case MoU = 'MoU';
    case MoA = 'MoA';
    case PKS = 'PKS';
    case IA = 'IA';

    public function label(): string
    {
        return match ($this) {
            self::MoU => 'MoU (Nota Kesepahaman)',
            self::MoA => 'MoA (Nota Kesepakatan)',
            self::PKS => 'PKS (Perjanjian Kerja Sama)',
            self::IA => 'IA (Implementation Arrangement)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MoU => 'info',
            self::MoA => 'success',
            self::PKS => 'warning',
            self::IA => 'primary',
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
