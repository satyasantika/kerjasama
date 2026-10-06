<?php

namespace App\Filament\Resources\KerjaSama\RelationManagers;

use App\Filament\Resources\KerjaSama\KerjaSamaResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnakRelationManager extends RelationManager
{
    protected static string $relationship = 'anak';

    protected static ?string $title = 'Dokumen turunan';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('judul')
            ->columns([
                TextColumn::make('judul')->label('Judul')->wrap(),
                TextColumn::make('jenis_dokumen')->label('Jenis')->badge(),
                TextColumn::make('tanggal_berakhir')->label('Berakhir')->date('d/m/Y'),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->recordActions([
                ViewAction::make()->url(fn ($record) => KerjaSamaResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
