<?php

namespace App\Enums;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusKerjaSama: string implements HasColor, HasLabel
{
    case Dibatalkan = 'dibatalkan';
    case Dihentikan = 'dihentikan';
    case BelumBerlaku = 'belum_berlaku';
    case Kedaluwarsa = 'kedaluwarsa';
    case AkanBerakhir = 'akan_berakhir';
    case Aktif = 'aktif';

    /**
     * Status dihitung, tidak disimpan (README §7). Urutan prioritas:
     * manual, belum berlaku, kedaluwarsa, akan berakhir, aktif.
     */
    public static function hitung(
        ?StatusManual $manual,
        CarbonInterface $mulai,
        CarbonInterface $berakhir,
        ?CarbonInterface $hari = null,
    ): self {
        // Bandingkan sebagai tanggal kalender agar tidak terpengaruh zona waktu/jam.
        $hari = Carbon::parse(($hari ?? now())->toDateString());
        $mulai = Carbon::parse($mulai->toDateString());
        $berakhir = Carbon::parse($berakhir->toDateString());

        return match (true) {
            $manual === StatusManual::Dibatalkan => self::Dibatalkan,
            $manual === StatusManual::Dihentikan => self::Dihentikan,
            $hari->lt($mulai) => self::BelumBerlaku,
            $hari->gt($berakhir) => self::Kedaluwarsa,
            $hari->diffInDays($berakhir) <= self::ambangAkanBerakhir() => self::AkanBerakhir,
            default => self::Aktif,
        };
    }

    public static function ambangAkanBerakhir(): int
    {
        return (int) config('kerjasama.ambang_akan_berakhir', 90);
    }

    public function label(): string
    {
        return match ($this) {
            self::Dibatalkan => 'Dibatalkan',
            self::Dihentikan => 'Dihentikan',
            self::BelumBerlaku => 'Belum berlaku',
            self::Kedaluwarsa => 'Kedaluwarsa',
            self::AkanBerakhir => 'Akan berakhir',
            self::Aktif => 'Aktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Dibatalkan => 'danger',
            self::Dihentikan => 'gray',
            self::BelumBerlaku => 'info',
            self::Kedaluwarsa => 'danger',
            self::AkanBerakhir => 'warning',
            self::Aktif => 'success',
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
