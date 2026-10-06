<?php

namespace App\Filament\Resources\Mitra\Pages;

use App\Filament\Resources\Mitra\MitraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMitra extends ListRecords
{
    protected static string $resource = MitraResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
