<?php

namespace App\Filament\Resources\Pengguna\Schemas;

use App\Enums\Peran;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PenggunaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Alamat email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('password')
                ->label('Kata sandi')
                ->password()
                ->revealable()
                ->minLength(8)
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->helperText(fn (string $operation): ?string => $operation === 'edit'
                    ? 'Kosongkan bila tidak ingin mengganti kata sandi.'
                    : 'Minimal 8 karakter. Pengguna dapat menggantinya sendiri lewat menu Profil.'),
            Select::make('peran')
                ->label('Peran')
                ->options(Peran::class)
                ->required()
                ->live(),
            Select::make('prodi_id')
                ->label('Program studi')
                ->relationship('prodi', 'nama')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => in_array(self::peran($get), [Peran::AdminProdi, Peran::Pimpinan], true))
                ->required(fn (Get $get): bool => self::peran($get) === Peran::AdminProdi)
                ->helperText(fn (Get $get): ?string => self::peran($get) === Peran::Pimpinan
                    ? 'Opsional. Isi bila pimpinan hanya mencakup satu program studi.'
                    : null),
            Toggle::make('aktif')
                ->label('Akun aktif')
                ->helperText('Akun nonaktif tidak dapat masuk ke sistem.')
                ->default(true),
        ]);
    }

    private static function peran(Get $get): ?Peran
    {
        $nilai = $get('peran');

        return $nilai instanceof Peran ? $nilai : Peran::tryFrom((string) $nilai);
    }
}
