<?php

namespace App\Filament\Resources\KerjaSama\Pages;

use App\Filament\Resources\KerjaSama\KerjaSamaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKerjaSama extends ViewRecord
{
    protected static string $resource = KerjaSamaResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
