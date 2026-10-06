<?php

namespace App\Filament\Resources\RealisasiKegiatan\Pages;

use App\Enums\StatusVerifikasi;
use App\Filament\Concerns\MenyimpanProdiRealisasi;
use App\Filament\Resources\RealisasiKegiatan\RealisasiKegiatanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRealisasiKegiatan extends CreateRecord
{
    use MenyimpanProdiRealisasi;

    protected static string $resource = RealisasiKegiatanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['dibuat_oleh'] = auth()->id();
        $data['status_verifikasi'] = StatusVerifikasi::Draf->value;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->simpanProdi($this->getRecord(), $this->form->getRawState());
    }

    protected function getRedirectUrl(): string
    {
        return RealisasiKegiatanResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
