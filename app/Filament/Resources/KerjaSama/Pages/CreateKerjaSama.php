<?php

namespace App\Filament\Resources\KerjaSama\Pages;

use App\Filament\Concerns\MenyimpanProdiKerjaSama;
use App\Filament\Resources\KerjaSama\KerjaSamaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKerjaSama extends CreateRecord
{
    use MenyimpanProdiKerjaSama;

    protected static string $resource = KerjaSamaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['dibuat_oleh'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->simpanProdi($this->getRecord(), $this->form->getRawState());
        $this->getRecord()->terapkanAturanMitraAsing();
    }
}
