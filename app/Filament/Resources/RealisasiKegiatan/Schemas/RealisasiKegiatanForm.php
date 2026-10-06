<?php

namespace App\Filament\Resources\RealisasiKegiatan\Schemas;

use App\Enums\Dharma;
use App\Models\BentukKerjaSama;
use App\Models\Prodi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RealisasiKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kegiatan')
                ->columns(2)
                ->schema([
                    Select::make('kerja_sama_id')
                        ->label('Kerja sama')
                        ->relationship('kerjaSama', 'judul')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => request()->query('kerja_sama_id'))
                        ->columnSpanFull(),
                    TextInput::make('judul_kegiatan')->label('Judul kegiatan')->required()->maxLength(255)->columnSpanFull(),
                    Textarea::make('deskripsi')->label('Deskripsi')->rows(3)->columnSpanFull(),
                    Select::make('bentuk_kerja_sama_id')
                        ->label('Bentuk kerja sama')
                        ->relationship('bentuk', 'nama', fn ($query) => $query->where('aktif', true)->orderBy('kode'))
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode} — {$record->nama}")
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            $default = $state ? BentukKerjaSama::find($state)?->dharma_default : null;
                            if ($default) {
                                $set('dharma', $default->value);
                            }
                        }),
                    Select::make('dharma')->label('Dharma')->options(Dharma::class)->required(),
                    DatePicker::make('tanggal_mulai')->label('Tanggal mulai')->required()->native(false)->displayFormat('d/m/Y')->live(),
                    DatePicker::make('tanggal_selesai')
                        ->label('Tanggal selesai')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->afterOrEqual('tanggal_mulai'),
                    Select::make('prodi_ids')
                        ->label('Program studi terkait')
                        ->options(fn () => Prodi::where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                        ->multiple()
                        ->searchable()
                        ->helperText('Prodi Anda otomatis ikut disertakan untuk admin prodi.')
                        ->columnSpanFull(),
                ]),
            Section::make('Hasil')
                ->columns(2)
                ->schema([
                    Textarea::make('manfaat_bagi_prodi')->label('Manfaat bagi program studi')->required()->rows(3)->columnSpanFull(),
                    Textarea::make('luaran')->label('Luaran (produk/hasil)')->rows(2)->columnSpanFull(),
                    TextInput::make('jumlah_mahasiswa')->label('Jumlah mahasiswa')->numeric()->minValue(0)->maxValue(65535)->default(0)->required(),
                    TextInput::make('jumlah_dosen')->label('Jumlah dosen')->numeric()->minValue(0)->maxValue(65535)->default(0)->required(),
                ]),
        ]);
    }
}
