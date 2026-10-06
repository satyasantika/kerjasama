<?php

namespace App\Filament\Resources\RealisasiKegiatan\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RealisasiKegiatanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kegiatan')
                ->columns(3)
                ->schema([
                    TextEntry::make('judul_kegiatan')->label('Judul')->columnSpan(2),
                    TextEntry::make('status_verifikasi')->label('Status')->badge(),
                    TextEntry::make('kerjaSama.judul')->label('Kerja sama')->columnSpanFull(),
                    TextEntry::make('deskripsi')->label('Deskripsi')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('dharma')->label('Dharma')->badge(),
                    TextEntry::make('bentuk.nama')->label('Bentuk')->placeholder('—'),
                    TextEntry::make('prodi.nama')->label('Prodi')->badge(),
                    TextEntry::make('tanggal_mulai')->label('Mulai')->date('d/m/Y'),
                    TextEntry::make('tanggal_selesai')->label('Selesai')->date('d/m/Y')->placeholder('—'),
                ]),
            Section::make('Hasil')
                ->columns(2)
                ->schema([
                    TextEntry::make('manfaat_bagi_prodi')->label('Manfaat bagi prodi')->columnSpanFull(),
                    TextEntry::make('luaran')->label('Luaran')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('jumlah_mahasiswa')->label('Mahasiswa'),
                    TextEntry::make('jumlah_dosen')->label('Dosen'),
                ]),
            Section::make('Verifikasi')
                ->columns(3)
                ->schema([
                    TextEntry::make('verifikator.name')->label('Diverifikasi oleh')->placeholder('—'),
                    TextEntry::make('diverifikasi_pada')->label('Pada')->dateTime('d/m/Y H:i')->placeholder('—'),
                    TextEntry::make('catatan_verifikasi')->label('Catatan')->placeholder('—'),
                ]),
        ]);
    }
}
