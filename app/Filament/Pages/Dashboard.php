<?php

namespace App\Filament\Pages;

use App\Models\KerjaSama;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('laporanPdf')
                ->label('Unduh rekap PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(route('laporan.rekap-pimpinan'))
                ->visible(fn () => auth()->user()?->can('viewAny', KerjaSama::class) ?? false),
        ];
    }
}
