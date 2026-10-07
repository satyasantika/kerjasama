<?php

namespace App\Filament\Resources\KerjaSama\Schemas;

use App\Enums\BidangKerjaSama;
use App\Enums\JenisDokumen;
use App\Enums\PihakPenandatangan;
use App\Enums\StatusManual;
use App\Enums\TingkatKerjaSama;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\Prodi;
use App\Rules\TautanBerkasValid;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class KerjaSamaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([
                self::langkahIdentitas(),
                self::langkahParaPihak(),
                self::langkahMasaBerlaku(),
                self::langkahBerkas(),
            ])->skippable()->columnSpanFull(),
        ]);
    }

    private static function langkahIdentitas(): Step
    {
        return Step::make('Identitas dokumen')
            ->columns(2)
            ->schema([
                Select::make('jenis_dokumen')->label('Jenis dokumen')->options(JenisDokumen::class)->required()->live(),
                Radio::make('lingkup_mitra')
                    ->label('Lingkup mitra')
                    ->options(['dalam_negeri' => 'Dalam negeri', 'luar_negeri' => 'Luar negeri'])
                    ->default('dalam_negeri')
                    ->inline()
                    ->live()
                    ->dehydrated(false)
                    ->visible(fn (Get $get): bool => self::adalahIA($get))
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state === 'luar_negeri') {
                            $set('induk_id', null);
                        }
                    })
                    ->helperText('Dalam negeri: dasar kerja sama (dokumen induk) wajib diisi. Luar negeri: tidak ditanyakan.'),
                Select::make('induk_id')
                    ->label(fn (Get $get): string => self::adalahIA($get) ? 'Dasar kerja sama (dokumen induk)' : 'Dokumen induk')
                    ->helperText(fn (Get $get): string => self::adalahIA($get)
                        ? 'Pilih MoU/MoA/PKS yang menjadi dasar IA ini. Mitra dan prodi terisi otomatis dari dokumen tersebut.'
                        : 'Isi bila ini dokumen turunan (mis. MoA di bawah MoU).')
                    ->relationship('induk', 'judul', fn ($query, ?KerjaSama $record) => $query
                        ->where('jenis_dokumen', '!=', JenisDokumen::IA->value)
                        ->when($record, fn ($q) => $q->whereKeyNot($record->getKey())))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->visible(fn (Get $get): bool => ! self::adalahIA($get)
                        || $get('lingkup_mitra') !== 'luar_negeri'
                        || filled($get('induk_id')))
                    ->required(fn (Get $get): bool => self::adalahIA($get) && $get('lingkup_mitra') !== 'luar_negeri')
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if (! self::adalahIA($get) || blank($state)) {
                            return;
                        }

                        $induk = KerjaSama::with(['mitra', 'prodi'])->find($state);

                        if ($induk === null) {
                            return;
                        }

                        $set('mitra', $induk->mitra->pluck('id')->all());
                        $set('prodi_ids', $induk->prodi->pluck('id')->all());
                        $set('tingkat', $induk->tingkat->value);
                        $set('bidang', $induk->bidang->value);
                    }),
                TextInput::make('nomor_dokumen_unsil')->label('Nomor dokumen Unsil')->maxLength(255),
                TextInput::make('nomor_dokumen_mitra')->label('Nomor dokumen mitra')->maxLength(255),
                TextInput::make('judul')->label('Judul')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('ruang_lingkup')->label('Ruang lingkup')->required()->rows(4)->columnSpanFull(),
                Select::make('bidang')->label('Bidang')->options(BidangKerjaSama::class)->required(),
                Select::make('tingkat')
                    ->label('Tingkat')
                    ->options(TingkatKerjaSama::class)
                    ->required()
                    ->helperText(fn (Get $get): ?string => self::adaMitraAsing($get('mitra'))
                        ? 'Otomatis internasional karena ada mitra luar negeri.' : null),
                Select::make('bentuk')
                    ->label('Bentuk kerja sama')
                    ->relationship('bentuk', 'nama', fn ($query) => $query->where('aktif', true)->orderBy('kode'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode} — {$record->nama}")
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }

    private static function langkahParaPihak(): Step
    {
        return Step::make('Para pihak & prodi')
            ->columns(2)
            ->schema([
                Select::make('mitra')
                    ->label('Mitra')
                    ->relationship('mitra', 'nama')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->rule(fn (Get $get): Closure => function (string $attribute, $value, Closure $gagal) use ($get) {
                        if (self::adalahIA($get) && $get('lingkup_mitra') === 'luar_negeri' && ! self::adaMitraAsing($value)) {
                            $gagal('IA luar negeri harus memiliki minimal satu mitra luar negeri. Ganti lingkup ke dalam negeri bila mitranya di Indonesia.');
                        }
                    })
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if (self::adaMitraAsing($state)) {
                            $set('tingkat', TingkatKerjaSama::Internasional->value);
                            $set('perlu_persetujuan_dirjen', true);

                            if (self::adalahIA($get)) {
                                $set('lingkup_mitra', 'luar_negeri');
                                $set('induk_id', null);
                            }
                        }
                    })
                    ->columnSpanFull(),
                Select::make('prodi_ids')
                    ->label('Program studi terlibat')
                    ->options(fn () => Prodi::where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                    ->multiple()
                    ->searchable()
                    ->helperText('Prodi Anda otomatis ikut disertakan untuk admin prodi.'),
                Select::make('prodi_penginisiasi_ids')
                    ->label('Prodi penginisiasi')
                    ->options(fn () => Prodi::where('aktif', true)->orderBy('nama')->pluck('nama', 'id'))
                    ->multiple()
                    ->searchable(),
                Select::make('pihak_penandatangan_unsil')
                    ->label('Penandatangan Unsil (jabatan)')
                    ->options(PihakPenandatangan::class)
                    ->required()
                    ->live()
                    ->helperText(fn (Get $get): ?string => self::adaMitraAsing($get('mitra')) && $get('pihak_penandatangan_unsil') !== PihakPenandatangan::Rektor->value
                        ? 'Peringatan: kerja sama dengan pihak luar negeri lazimnya ditandatangani rektor (Permendikbud 14/2014 Ps. 48).' : null),
                TextInput::make('nama_penandatangan_unsil')->label('Nama penandatangan Unsil')->maxLength(255),
                TextInput::make('nama_penandatangan_mitra')->label('Nama penandatangan mitra')->maxLength(255),
                TextInput::make('jabatan_penandatangan_mitra')->label('Jabatan penandatangan mitra')->maxLength(255),
            ]);
    }

    private static function langkahMasaBerlaku(): Step
    {
        return Step::make('Masa berlaku')
            ->columns(2)
            ->schema([
                DatePicker::make('tanggal_tanda_tangan')->label('Tanggal tanda tangan')->required()->native(false)->displayFormat('d/m/Y')->live(),
                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal mulai')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->helperText(fn (Get $get): ?string => self::berlakuSurut($get('tanggal_tanda_tangan'), $get('tanggal_mulai'))
                        ? 'Peringatan: tanggal mulai lebih awal dari tanggal tanda tangan (berlaku surut).' : null),
                DatePicker::make('tanggal_berakhir')
                    ->label('Tanggal berakhir')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('tanggal_mulai')
                    ->after('tanggal_mulai')
                    ->live()
                    ->helperText(fn (Get $get): ?string => self::melewatiInduk($get('induk_id'), $get('tanggal_berakhir'))
                        ? 'Peringatan: berakhir setelah dokumen induknya.' : null),
                Select::make('status_manual')
                    ->label('Status manual')
                    ->options(StatusManual::class)
                    ->placeholder('(dihitung otomatis)')
                    ->helperText('Isi hanya bila kerja sama dibatalkan atau dihentikan sebelum waktunya.'),
                Toggle::make('memuat_hki_aset')->label('Memuat HKI/aset negara')->helperText('Permendikbud 14/2014 Ps. 47 ay. 3.'),
                Toggle::make('perlu_persetujuan_dirjen')->label('Perlu persetujuan Dirjen')->helperText('Ps. 49 ay. 1; otomatis bila ada mitra luar negeri.'),
                Toggle::make('sudah_dilaporkan_pddikti')->label('Sudah dilaporkan ke PDDikti')->helperText('Ps. 49 ay. 3.'),
            ]);
    }

    private static function langkahBerkas(): Step
    {
        return Step::make('Berkas')
            ->schema([
                TextInput::make('tautan_dokumen')
                    ->label('Tautan dokumen perjanjian (Google Drive)')
                    ->helperText('Tempel tautan berbagi Google Drive PDF naskah bertanda tangan. Disarankan dibagikan terbatas pada akun unsil.ac.id.')
                    ->url()
                    ->maxLength(500)
                    ->rule(new TautanBerkasValid)
                    ->placeholder('https://drive.google.com/file/d/…'),
                TextInput::make('tautan_dokumen_asing')
                    ->label('Tautan dokumen versi bahasa asing (Google Drive)')
                    ->helperText('Wajib dibuat bila ada pihak asing (Permendikbud 14/2014 Ps. 47 ay. 4).')
                    ->url()
                    ->maxLength(500)
                    ->rule(new TautanBerkasValid)
                    ->visible(fn (Get $get): bool => self::adaMitraAsing($get('mitra'))),
                FileUpload::make('berkas_dokumen')
                    ->label('Dokumen perjanjian (PDF)')
                    ->disk('local')
                    ->directory('kerja-sama')
                    ->visibility('private')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize((int) config('kerjasama.maks_berkas_pdf_kb'))
                    ->openable(false)
                    ->downloadable(false)
                    ->previewable(false)
                    ->visible(fn (): bool => config('berkas.unggah_aktif')),
                FileUpload::make('berkas_dokumen_asing')
                    ->label('Dokumen versi bahasa asing (PDF)')
                    ->helperText('Wajib dibuat bila ada pihak asing (Permendikbud 14/2014 Ps. 47 ay. 4).')
                    ->disk('local')
                    ->directory('kerja-sama')
                    ->visibility('private')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize((int) config('kerjasama.maks_berkas_pdf_kb'))
                    ->openable(false)
                    ->downloadable(false)
                    ->previewable(false)
                    ->visible(fn (Get $get): bool => config('berkas.unggah_aktif') && self::adaMitraAsing($get('mitra'))),
            ]);
    }

    private static function adalahIA(Get $get): bool
    {
        $jenis = $get('jenis_dokumen');

        return ($jenis instanceof JenisDokumen ? $jenis : JenisDokumen::tryFrom((string) $jenis)) === JenisDokumen::IA;
    }

    /** @param  array<int|string>|null  $mitraIds */
    public static function adaMitraAsing(?array $mitraIds): bool
    {
        return filled($mitraIds)
            && Mitra::whereIn('id', $mitraIds)->where('negara', '!=', 'Indonesia')->exists();
    }

    public static function berlakuSurut(mixed $tandaTangan, mixed $mulai): bool
    {
        return filled($tandaTangan) && filled($mulai)
            && Carbon::parse($mulai)->lt(Carbon::parse($tandaTangan));
    }

    public static function melewatiInduk(mixed $indukId, mixed $berakhir): bool
    {
        if (blank($indukId) || blank($berakhir)) {
            return false;
        }

        $induk = KerjaSama::find($indukId);

        return $induk !== null && Carbon::parse($berakhir)->gt($induk->tanggal_berakhir);
    }
}
