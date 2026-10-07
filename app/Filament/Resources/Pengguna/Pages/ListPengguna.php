<?php

namespace App\Filament\Resources\Pengguna\Pages;

use App\Actions\Pengguna\ImporPengguna;
use App\Filament\Resources\Pengguna\PenggunaResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListPengguna extends ListRecords
{
    protected static string $resource = PenggunaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imporPengguna')
                ->label('Impor massal')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Impor pengguna massal')
                ->modalDescription('Unggah CSV atau XLSX dengan kolom: nama, email, peran, kode_prodi, kata_sandi (opsional). Peran: admin_fakultas, admin_prodi, atau pimpinan. Bila ada satu baris tidak sah, tidak ada akun yang dibuat. Berkas hanya diproses lalu dibuang.')
                ->modalSubmitActionLabel('Impor')
                ->extraModalFooterActions([
                    Action::make('unduhTemplat')
                        ->label('Unduh templat')
                        ->color('gray')
                        ->action(fn () => $this->unduhTemplat()),
                ])
                ->schema([
                    FileUpload::make('berkas')
                        ->label('Berkas CSV/XLSX')
                        ->required()
                        ->storeFiles(false)
                        ->acceptedFileTypes([
                            'text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(1024),
                ])
                ->action(function (array $data) {
                    $berkas = $data['berkas'];

                    try {
                        $hasil = app(ImporPengguna::class)->handle(
                            $berkas->getRealPath(),
                            $berkas->getClientOriginalExtension(),
                            auth()->user(),
                        );
                    } catch (RuntimeException $e) {
                        Notification::make()->danger()->title('Impor gagal')->body($e->getMessage())->persistent()->send();

                        return null;
                    }

                    if ($hasil['galat'] !== []) {
                        $tampil = array_slice($hasil['galat'], 0, 10);
                        $sisa = count($hasil['galat']) - count($tampil);

                        Notification::make()->danger()
                            ->title('Impor dibatalkan: tidak ada akun yang dibuat')
                            ->body(implode("\n", $tampil).($sisa > 0 ? "\n…dan {$sisa} masalah lain." : ''))
                            ->persistent()
                            ->send();

                        return null;
                    }

                    Notification::make()->success()
                        ->title(count($hasil['hasil']).' pengguna berhasil dibuat')
                        ->body('Berkas kredensial terunduh otomatis. Simpan dengan aman lalu hapus; kata sandi dibangkitkan hanya ditampilkan di sini.')
                        ->send();

                    return $this->unduhKredensial($hasil['hasil']);
                }),
            CreateAction::make()->label('Tambah pengguna'),
        ];
    }

    private function unduhTemplat(): BinaryFileResponse
    {
        return $this->tulisXlsx('templat-impor-pengguna.xlsx', [
            ImporPengguna::KOLOM,
            ['CONTOH (baris ini dilewati)', 'contoh@unsil.ac.id', 'admin_prodi', 'PMAT', ''],
            ['CONTOH', 'dekan@unsil.ac.id', 'pimpinan', '', 'sandi-awal-123'],
        ]);
    }

    /** @param  list<array<string, string>>  $hasil */
    private function unduhKredensial(array $hasil): BinaryFileResponse
    {
        return $this->tulisXlsx(
            'kredensial-pengguna-'.now()->format('Ymd-His').'.xlsx',
            [['nama', 'email', 'peran', 'program_studi', 'kata_sandi'], ...array_map('array_values', $hasil)],
        );
    }

    /** @param  list<list<string>>  $baris */
    private function tulisXlsx(string $nama, array $baris): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'pengguna');
        $writer = new Writer;
        $writer->openToFile($path);

        foreach ($baris as $b) {
            $writer->addRow(Row::fromValues($b));
        }

        $writer->close();

        return response()->download($path, $nama, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
