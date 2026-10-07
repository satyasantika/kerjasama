<?php

namespace App\Filament\Resources\Pengguna\Pages;

use App\Actions\Pengguna\SimpanPengguna;
use App\Filament\Resources\Pengguna\PenggunaResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPengguna extends EditRecord
{
    protected static string $resource = PenggunaResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['peran'] = $this->getRecord()->roles()->first()?->name;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(SimpanPengguna::class)->handle($record, $data, auth()->user());
    }
}
