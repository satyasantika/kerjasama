<?php

namespace App\Filament\Resources\Pengguna\Tables;

use App\Enums\Peran;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PenggunaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['roles', 'prodi']))
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Peran::tryFrom($state)?->label() ?? $state)
                    ->color(fn (string $state): string => Peran::tryFrom($state)?->color() ?? 'gray'),
                TextColumn::make('prodi.nama')->label('Program studi')->placeholder('—'),
                IconColumn::make('aktif')->label('Aktif')->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('peran')
                    ->label('Peran')
                    ->options(Peran::class)
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->role($data['value'])
                        : $query),
                TernaryFilter::make('aktif')->label('Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
