<?php

namespace App\Filament\Resources\KerjaSama\Pages;

use App\Filament\Concerns\MenyimpanProdiKerjaSama;
use App\Filament\Resources\KerjaSama\KerjaSamaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditKerjaSama extends EditRecord
{
    use MenyimpanProdiKerjaSama;

    protected static string $resource = KerjaSamaResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['lingkup_mitra'] = $this->getRecord()->punyaMitraAsing() ? 'luar_negeri' : 'dalam_negeri';

        return $this->isiProdiVirtual($data);
    }

    protected function afterSave(): void
    {
        $this->simpanProdi($this->getRecord(), $this->form->getRawState());
        $this->getRecord()->terapkanAturanMitraAsing();
    }
}
