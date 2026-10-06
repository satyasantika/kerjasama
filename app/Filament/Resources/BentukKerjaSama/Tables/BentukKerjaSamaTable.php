<?php

namespace App\Filament\Resources\BentukKerjaSama\Tables;

use App\Enums\BidangBentuk;
use App\Enums\Dharma;
use App\Enums\RuangKerjaSama;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BentukKerjaSamaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->label('Kode')->searchable()->sortable(),
                TextColumn::make('nama')->label('Nama')->searchable()->wrap(),
                TextColumn::make('bidang')->label('Bidang')->badge(),
                TextColumn::make('ruang')->label('Ruang')->badge(),
                TextColumn::make('dharma_default')->label('Dharma')->badge()->placeholder('—'),
                TextColumn::make('pasal_rujukan')->label('Pasal')->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('aktif')->label('Aktif')->boolean(),
            ])
            ->defaultSort('kode')
            ->filters([
                SelectFilter::make('bidang')->label('Bidang')->options(BidangBentuk::class),
                SelectFilter::make('ruang')->label('Ruang')->options(RuangKerjaSama::class),
                SelectFilter::make('dharma_default')->label('Dharma')->options(Dharma::class),
                TernaryFilter::make('aktif')->label('Aktif'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
