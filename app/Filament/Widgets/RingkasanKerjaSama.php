<?php

namespace App\Filament\Widgets;

use App\Enums\StatusKerjaSama;
use App\Models\KerjaSama;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RingkasanKerjaSama extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Ringkasan kerja sama';

    public static function canView(): bool
    {
        return auth()->user()?->mengurusKerjaSama() ?? false;
    }

    protected function getStats(): array
    {
        $ambang = StatusKerjaSama::ambangAkanBerakhir();

        return [
            Stat::make('Aktif', KerjaSama::aktif()->count())
                ->description('Berlaku, sisa lebih dari '.$ambang.' hari')
                ->color('success'),
            Stat::make('Akan berakhir', KerjaSama::akanBerakhir()->count())
                ->description('Berakhir dalam '.$ambang.' hari')
                ->color('warning'),
            Stat::make('Kedaluwarsa', KerjaSama::kedaluwarsa()->count())
                ->description('Masa berlaku sudah lewat')
                ->color('danger'),
        ];
    }
}
