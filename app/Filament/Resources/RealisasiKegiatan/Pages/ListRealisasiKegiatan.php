<?php

namespace App\Filament\Resources\RealisasiKegiatan\Pages;

use App\Filament\Resources\RealisasiKegiatan\RealisasiKegiatanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiKegiatan extends ListRecords
{
    protected static string $resource = RealisasiKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
