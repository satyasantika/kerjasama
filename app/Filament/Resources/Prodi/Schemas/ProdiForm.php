<?php

namespace App\Filament\Resources\Prodi\Schemas;

use App\Enums\JenjangProdi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProdiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('kode')
                ->label('Kode')
                ->required()
                ->maxLength(20)
                ->unique(ignoreRecord: true),
            TextInput::make('nama')
                ->label('Nama program studi')
                ->required()
                ->maxLength(255),
            Select::make('jenjang')
                ->label('Jenjang')
                ->options(JenjangProdi::class)
                ->required(),
            Toggle::make('aktif')
                ->label('Aktif')
                ->default(true),
        ]);
    }
}
