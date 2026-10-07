<?php

namespace App\Filament\Resources\Mitra\Tables;

use App\Enums\JenisMitra;
use App\Models\Mitra;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MitraTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->label('Nama')->searchable()->sortable(),
                TextColumn::make('jenis')->label('Jenis')->badge(),
                TextColumn::make('negara')->label('Negara')->searchable()->sortable(),
                TextColumn::make('provinsi')->label('Provinsi')->searchable()->toggleable(),
                TextColumn::make('kontak_nama')->label('Kontak')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama')
            ->filters([
                SelectFilter::make('jenis')->label('Jenis')->options(JenisMitra::class),
                SelectFilter::make('negara')->label('Negara')
                    ->options(fn () => Mitra::query()->distinct()->orderBy('negara')->pluck('negara', 'negara')->all()),
                TrashedFilter::make()->label('Data terhapus'),
            ])
            ->recordActions([
                EditAction::make(),
                RestoreAction::make(),
            ]);
    }
}
