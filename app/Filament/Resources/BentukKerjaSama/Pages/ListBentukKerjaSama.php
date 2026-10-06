<?php

namespace App\Filament\Resources\BentukKerjaSama\Pages;

use App\Filament\Resources\BentukKerjaSama\BentukKerjaSamaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBentukKerjaSama extends ListRecords
{
    protected static string $resource = BentukKerjaSamaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
