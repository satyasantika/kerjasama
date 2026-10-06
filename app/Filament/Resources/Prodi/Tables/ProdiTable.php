<?php

namespace App\Filament\Resources\Prodi\Tables;

use App\Enums\JenjangProdi;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProdiTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->label('Kode')->searchable()->sortable(),
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('jenjang')->label('Jenjang')->badge(),
                IconColumn::make('aktif')->label('Aktif')->boolean(),
            ])
            ->defaultSort('kode')
            ->filters([
                SelectFilter::make('jenjang')->label('Jenjang')->options(JenjangProdi::class),
                TernaryFilter::make('aktif')->label('Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
