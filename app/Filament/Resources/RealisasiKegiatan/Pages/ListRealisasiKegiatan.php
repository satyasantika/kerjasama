<?php

namespace App\Filament\Resources\RealisasiKegiatan\Pages;

use App\Filament\Resources\RealisasiKegiatan\RealisasiKegiatanResource;
use App\Models\Prodi;
use App\Services\EksporKerjaSamaTridharma;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiKegiatan extends ListRecords
{
    protected static string $resource = RealisasiKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('eksporTridharma')
                ->label('Ekspor Tabel Tridharma')
                ->icon('heroicon-o-arrow-down-tray')
                ->modalHeading('Ekspor Tabel Kerja Sama Tridharma')
                ->modalDescription('Tiga lembar (Pendidikan, Penelitian, PkM) berisi realisasi kegiatan terverifikasi pada rentang TS-2 s.d. TS.')
                ->modalSubmitActionLabel('Unduh')
                ->schema([
                    Select::make('prodi_id')
                        ->label('Program studi')
                        ->options(fn () => Prodi::orderBy('nama')->pluck('nama', 'id'))
                        ->default(fn () => auth()->user()?->prodi_id)
                        ->searchable()
                        ->required(),
                    TextInput::make('tahun_ts')
                        ->label('Tahun TS')
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(2100)
                        ->default(now()->year)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $prodi = Prodi::findOrFail($data['prodi_id']);
                    $ts = (int) $data['tahun_ts'];
                    $nama = 'tabel-kerja-sama-tridharma-'.str($prodi->kode)->slug().'-TS'.$ts.'.xlsx';
                    $path = tempnam(sys_get_temp_dir(), 'tridharma');

                    (new EksporKerjaSamaTridharma)->tulis($prodi, $ts, $path);

                    return response()->download($path, $nama, [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])->deleteFileAfterSend();
                }),
            CreateAction::make(),
        ];
    }
}
