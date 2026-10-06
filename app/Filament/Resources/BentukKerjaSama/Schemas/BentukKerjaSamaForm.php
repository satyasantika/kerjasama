<?php

namespace App\Filament\Resources\BentukKerjaSama\Schemas;

use App\Enums\BidangBentuk;
use App\Enums\Dharma;
use App\Enums\RuangKerjaSama;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BentukKerjaSamaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('kode')->label('Kode')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('nama')->label('Nama bentuk')->required()->maxLength(255),
            Select::make('bidang')->label('Bidang')->options(BidangBentuk::class)->required(),
            Select::make('ruang')->label('Ruang')->options(RuangKerjaSama::class)->required(),
            Select::make('dharma_default')->label('Dharma bawaan')->options(Dharma::class),
            TextInput::make('pasal_rujukan')->label('Pasal rujukan')->required()->maxLength(255),
            Toggle::make('aktif')->label('Aktif')->default(true),
        ]);
    }
}
