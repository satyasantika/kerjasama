<?php

namespace App\Filament\Resources\Mitra\Schemas;

use App\Enums\JenisMitra;
use App\Models\Mitra;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class MitraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas')
                ->columns(2)
                ->schema([
                    TextInput::make('nama')
                        ->label('Nama lembaga')
                        ->required()
                        ->maxLength(255)
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule, callable $get) => $rule->where('negara', $get('negara')),
                        ),
                    Select::make('jenis')
                        ->label('Jenis mitra')
                        ->options(JenisMitra::class)
                        ->required(),
                    TextInput::make('negara')
                        ->label('Negara')
                        ->required()
                        ->default('Indonesia')
                        ->live(onBlur: true)
                        ->maxLength(255),
                    TextInput::make('status_legal')
                        ->label('Status legal')
                        ->helperText('Akreditasi PT luar negeri atau registrasi badan usaha (wajib bila mitra luar negeri).')
                        ->required(fn (Get $get): bool => filled($get('negara')) && strcasecmp(trim($get('negara')), 'Indonesia') !== 0)
                        ->maxLength(255),
                    TextInput::make('provinsi')->label('Provinsi')->maxLength(255),
                    TextInput::make('kabupaten_kota')->label('Kabupaten/kota')->maxLength(255),
                    Textarea::make('alamat')->label('Alamat')->columnSpanFull(),
                    TextInput::make('website')->label('Situs web')->url()->maxLength(255),
                ]),
            Section::make('Kontak')
                ->columns(2)
                ->schema([
                    TextInput::make('kontak_nama')->label('Nama kontak')->maxLength(255),
                    TextInput::make('kontak_jabatan')->label('Jabatan')->maxLength(255),
                    TextInput::make('kontak_email')->label('Surel')->email()->maxLength(255),
                    TextInput::make('kontak_telepon')->label('Telepon')->tel()->maxLength(255),
                ]),
            Textarea::make('catatan')->label('Catatan')->columnSpanFull(),
        ]);
    }
}
