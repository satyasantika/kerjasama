<?php

namespace App\Filament\Resources\Prodi\Pages;

use App\Filament\Resources\Prodi\ProdiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProdi extends ListRecords
{
    protected static string $resource = ProdiResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
