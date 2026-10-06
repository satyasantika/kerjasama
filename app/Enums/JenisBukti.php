<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisBukti: string implements HasColor, HasLabel
{
    case Laporan = 'laporan';
    case DaftarHadir = 'daftar_hadir';
    case Foto = 'foto';
    case SuratTugas = 'surat_tugas';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Laporan => 'Laporan',
            self::DaftarHadir => 'Daftar hadir',
            self::Foto => 'Foto',
            self::SuratTugas => 'Surat tugas',
            self::Lainnya => 'Lainnya',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Laporan => 'info',
            self::DaftarHadir => 'success',
            self::Foto => 'warning',
            self::SuratTugas => 'primary',
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
