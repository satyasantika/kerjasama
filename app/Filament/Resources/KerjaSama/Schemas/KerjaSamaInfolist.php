<?php

namespace App\Filament\Resources\KerjaSama\Schemas;

use App\Models\KerjaSama;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KerjaSamaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dokumen')
                ->columns(3)
                ->schema([
                    TextEntry::make('judul')->label('Judul')->columnSpan(2),
                    TextEntry::make('status')->label('Status')->badge(),
                    TextEntry::make('jenis_dokumen')->label('Jenis')->badge(),
                    TextEntry::make('nomor_dokumen_unsil')->label('Nomor Unsil')->placeholder('—'),
                    TextEntry::make('nomor_dokumen_mitra')->label('Nomor mitra')->placeholder('—'),
                    TextEntry::make('induk.judul')->label('Dokumen induk')->placeholder('—'),
                    TextEntry::make('bidang')->label('Bidang')->badge(),
                    TextEntry::make('tingkat')->label('Tingkat')->badge(),
                    TextEntry::make('ruang_lingkup')->label('Ruang lingkup')->columnSpanFull(),
                    TextEntry::make('bentuk.nama')->label('Bentuk kerja sama')->badge()->columnSpanFull(),
                ]),
            Section::make('Para pihak')
                ->columns(3)
                ->schema([
                    TextEntry::make('mitra.nama')->label('Mitra')->badge()->columnSpanFull(),
                    TextEntry::make('prodi.nama')->label('Prodi terlibat')->badge()->columnSpanFull(),
                    TextEntry::make('pihak_penandatangan_unsil')->label('Penandatangan Unsil')->badge(),
                    TextEntry::make('nama_penandatangan_unsil')->label('Nama')->placeholder('—'),
                    TextEntry::make('nama_penandatangan_mitra')->label('Penandatangan mitra')->placeholder('—'),
                ]),
            Section::make('Masa berlaku')
                ->columns(3)
                ->schema([
                    TextEntry::make('tanggal_tanda_tangan')->label('Tanda tangan')->date('d/m/Y'),
                    TextEntry::make('tanggal_mulai')->label('Mulai')->date('d/m/Y'),
                    TextEntry::make('tanggal_berakhir')->label('Berakhir')->date('d/m/Y'),
                    IconEntry::make('memuat_hki_aset')->label('Memuat HKI/aset')->boolean(),
                    IconEntry::make('perlu_persetujuan_dirjen')->label('Persetujuan Dirjen')->boolean(),
                    IconEntry::make('sudah_dilaporkan_pddikti')->label('Dilaporkan PDDikti')->boolean(),
                ]),
            Section::make('Berkas')
                ->schema([
                    Actions::make([
                        Action::make('bukaTautanDokumen')
                            ->label('Buka dokumen perjanjian (Google Drive)')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn (KerjaSama $record) => route('tautan.kerja-sama', [$record, 'dokumen']))
                            ->openUrlInNewTab()
                            ->visible(fn (KerjaSama $record) => filled($record->tautan_dokumen)),
                        Action::make('bukaTautanAsing')
                            ->label('Buka versi bahasa asing (Google Drive)')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn (KerjaSama $record) => route('tautan.kerja-sama', [$record, 'asing']))
                            ->openUrlInNewTab()
                            ->visible(fn (KerjaSama $record) => filled($record->tautan_dokumen_asing)),
                        Action::make('unduhDokumen')
                            ->label('Unduh dokumen perjanjian')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(fn (KerjaSama $record) => route('kerja-sama.berkas', [$record, 'dokumen']))
                            ->visible(fn (KerjaSama $record) => filled($record->berkas_dokumen)),
                        Action::make('unduhAsing')
                            ->label('Unduh versi bahasa asing')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(fn (KerjaSama $record) => route('kerja-sama.berkas', [$record, 'asing']))
                            ->visible(fn (KerjaSama $record) => filled($record->berkas_dokumen_asing)),
                    ]),
                    TextEntry::make('berkas_dokumen')->hiddenLabel()->default('Belum ada berkas.')
                        ->visible(fn (KerjaSama $record) => blank($record->berkas_dokumen) && blank($record->berkas_dokumen_asing)
                            && blank($record->tautan_dokumen) && blank($record->tautan_dokumen_asing)),
                ]),
        ]);
    }
}
