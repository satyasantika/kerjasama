<?php

namespace App\Filament\Resources\Pengguna\Tables;

use App\Actions\Pengguna\MulaiMenyamar;
use App\Enums\Peran;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
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
                Action::make('menyamar')
                    ->label('Masuk sebagai')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->color('gray')
                    ->visible(fn (User $record): bool => auth()->user()?->can('impersonate', $record) ?? false)
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => "Masuk sebagai {$record->name}?")
                    ->modalDescription('Anda akan melihat dan bertindak sebagai pengguna ini. Gunakan tombol "Kembali ke akun saya" untuk mengakhiri.')
                    ->action(function (User $record) {
                        app(MulaiMenyamar::class)->handle(auth()->user(), $record);

                        return redirect()->to(Filament::getUrl());
                    }),
                EditAction::make(),
            ]);
    }
}
