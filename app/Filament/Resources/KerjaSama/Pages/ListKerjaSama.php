<?php

namespace App\Filament\Resources\KerjaSama\Pages;

use App\Filament\Resources\KerjaSama\KerjaSamaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKerjaSama extends ListRecords
{
    protected static string $resource = KerjaSamaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
