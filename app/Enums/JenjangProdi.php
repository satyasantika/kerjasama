<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenjangProdi: string implements HasColor, HasLabel
{
    case S1 = 'S1';
    case S2 = 'S2';
    case S3 = 'S3';
    case PPG = 'PPG';
    case Profesi = 'Profesi';

    public function label(): string
    {
        return match ($this) {
            self::S1 => 'S1',
            self::S2 => 'S2',
            self::S3 => 'S3',
            self::PPG => 'PPG',
            self::Profesi => 'Profesi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::S1 => 'info',
            self::S2 => 'success',
            self::S3 => 'warning',
            self::PPG => 'primary',
            self::Profesi => 'gray',
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
