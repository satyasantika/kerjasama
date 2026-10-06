<?php

namespace App\Filament\Resources\RealisasiKegiatan\Pages;

use App\Filament\Concerns\MenyimpanProdiRealisasi;
use App\Filament\Resources\RealisasiKegiatan\RealisasiKegiatanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiKegiatan extends EditRecord
{
    use MenyimpanProdiRealisasi;

    protected static string $resource = RealisasiKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->isiProdiVirtual($data);
    }

    protected function afterSave(): void
    {
        $this->simpanProdi($this->getRecord(), $this->form->getRawState());
    }
}
