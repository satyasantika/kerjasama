<?php

namespace App\Filament\Resources\Pengguna\Pages;

use App\Actions\Pengguna\SimpanPengguna;
use App\Filament\Resources\Pengguna\PenggunaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePengguna extends CreateRecord
{
    protected static string $resource = PenggunaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SimpanPengguna::class)->handle(null, $data, auth()->user());
    }
}
