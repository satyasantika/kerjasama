<?php

namespace App\Filament\Resources\Mitra\Pages;

use App\Filament\Resources\Mitra\MitraResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMitra extends EditRecord
{
    protected static string $resource = MitraResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }
}
