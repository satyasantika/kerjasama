<?php

namespace App\Filament\Pages;

use App\Models\EvaluasiKerjaSama;
use App\Models\Prodi;
use App\Services\RekapKerjaSamaProdi;
use App\Services\SkorKerjaSamaLamdik;
use BackedEnum;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Simulasi skor elemen Kerja Sama Tridharma LAMDIK IAPSK 3.0 (rujukan §B).
 * Angka dapat diambil dari data sistem lalu diubah bebas untuk simulasi.
 */
class SimulasiSkorLamdik extends Page
{
    protected string $view = 'filament.pages.simulasi-skor-lamdik';

    protected static ?string $title = 'Simulasi Skor LAMDIK';

    protected static ?string $navigationLabel = 'Simulasi Skor LAMDIK';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 10;

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @var array{rk: float, a: float, b: float, skor_a: float, skor: float}|null */
    public ?array $hasil = null;

    public ?string $pesan = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->mengurusKerjaSama() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'tahun_ts' => now()->year,
            'prodi_id' => auth()->user()?->prodi_id,
            'n1' => 0, 'n2' => 0, 'n3' => 0, 'ndtps' => 0, 'ni' => 0, 'nn' => 0, 'nw' => 0, 'skor_b' => 1,
        ]);
        $this->hitung();
    }

    public function form(Schema $schema): Schema
    {
        $angka = fn (string $nama, string $label) => TextInput::make($nama)
            ->label($label)->numeric()->integer()->minValue(0)->default(0)->required()->live(onBlur: true)
            ->afterStateUpdated(fn () => $this->hitung());

        return $schema
            ->statePath('data')
            ->components([
                Section::make('Ambil dari data sistem')
                    ->description('Pilih prodi dan tahun TS, lalu ambil angka dari realisasi terverifikasi. Angka tetap dapat diubah untuk simulasi.')
                    ->columns(3)
                    ->schema([
                        Select::make('prodi_id')->label('Program studi')
                            ->options(fn () => Prodi::orderBy('nama')->pluck('nama', 'id'))->searchable(),
                        TextInput::make('tahun_ts')->label('Tahun TS')->numeric()->minValue(2000)->maxValue(2100)->default(now()->year),
                    ])
                    ->footerActions([
                        Action::make('ambilData')->label('Ambil dari data')->icon('heroicon-o-arrow-path')->action('ambilData'),
                    ]),
                Section::make('Indikator (a) — kuantitas dan tingkat, 3 tahun terakhir')
                    ->columns(4)
                    ->schema([
                        $angka('n1', 'N1 — pendidikan'),
                        $angka('n2', 'N2 — penelitian'),
                        $angka('n3', 'N3 — PkM'),
                        $angka('ndtps', 'NDTPS'),
                        $angka('ni', 'NI — internasional'),
                        $angka('nn', 'NN — nasional'),
                        $angka('nw', 'NW — wilayah/lokal'),
                    ]),
                Section::make('Indikator (b) — keefektifan')
                    ->schema([
                        Select::make('skor_b')->label('Skor (b)')
                            ->options(EvaluasiKerjaSama::DESKRIPTOR)
                            ->required()->live()
                            ->afterStateUpdated(fn () => $this->hitung())
                            ->helperText('Terisi dari rata-rata evaluasi kerja sama bila ada (dibulatkan ke skor terdekat); dapat diubah.'),
                    ]),
            ]);
    }

    public function ambilData(): void
    {
        $state = $this->form->getState();
        $prodi = filled($state['prodi_id'] ?? null) ? Prodi::find($state['prodi_id']) : null;
        $ts = (int) ($state['tahun_ts'] ?? now()->year);

        if (! $prodi) {
            $this->pesan = 'Pilih program studi terlebih dahulu.';
            $this->hasil = null;

            return;
        }

        $rekap = new RekapKerjaSamaProdi;
        $skorB = $rekap->skorB($prodi, $ts);

        $this->form->fill([
            'prodi_id' => $prodi->id,
            'tahun_ts' => $ts,
            ...$rekap->jumlah($prodi, $ts),
            'ndtps' => $rekap->ndtps($prodi, $ts) ?? 0,
            'skor_b' => $skorB === null ? ($state['skor_b'] ?? 1) : max(1, min(4, (int) round($skorB))),
        ]);

        $this->hitung();
    }

    public function hitung(): void
    {
        $d = $this->data;
        $this->pesan = null;
        $this->hasil = null;

        try {
            $this->hasil = (new SkorKerjaSamaLamdik)->hitung(
                (int) ($d['n1'] ?? 0), (int) ($d['n2'] ?? 0), (int) ($d['n3'] ?? 0), (int) ($d['ndtps'] ?? 0),
                (int) ($d['ni'] ?? 0), (int) ($d['nn'] ?? 0), (int) ($d['nw'] ?? 0), (float) ($d['skor_b'] ?? 1),
            );
        } catch (DomainException $e) {
            $this->pesan = $e->getMessage();
        }
    }
}
