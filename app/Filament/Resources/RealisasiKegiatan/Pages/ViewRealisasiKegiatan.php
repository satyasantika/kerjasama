<?php

namespace App\Filament\Resources\RealisasiKegiatan\Pages;

use App\Filament\Resources\RealisasiKegiatan\Actions\AksiVerifikasi;
use App\Filament\Resources\RealisasiKegiatan\RealisasiKegiatanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRealisasiKegiatan extends ViewRecord
{
    protected static string $resource = RealisasiKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            AksiVerifikasi::ajukan(),
            AksiVerifikasi::verifikasi(),
            AksiVerifikasi::tolak(),
        ];
    }
}
