<?php

namespace App\Services;

use App\Enums\BidangKerjaSama;
use App\Enums\Dharma;
use App\Enums\JenisDokumen;
use App\Enums\JenisMitra;
use App\Enums\JenjangProdi;
use App\Enums\PihakPenandatangan;
use App\Enums\StatusVerifikasi;
use App\Enums\TingkatKerjaSama;
use App\Models\BentukKerjaSama;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\Prodi;
use App\Models\RealisasiKegiatan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Impor awal dari CSV (contoh-data/README.md). Idempoten: kunci prodi.kode,
 * (mitra.nama, mitra.negara), kerja_sama.kode_impor, dan (kerja sama, judul, tanggal mulai)
 * untuk realisasi. Baris yang kolom pertamanya berawalan CONTOH dilewati.
 */
class ImporKerjaSama
{
    /** @var array<string, array{dibuat: int, diperbarui: int, dilewati: int}> */
    private array $hitung = [];

    /** @var list<string> */
    private array $galat = [];

    /** @var list<string> */
    private array $peringatan = [];

    public function __construct(private readonly string $folder, private readonly User $pembuat) {}

    /** @return array{hitung: array, galat: list<string>, peringatan: list<string>} */
    public function jalankan(): array
    {
        if (! is_dir($this->folder)) {
            throw new RuntimeException("Folder tidak ditemukan: {$this->folder}");
        }

        $this->prodi();
        $this->mitra();
        $this->kerjaSama();
        $this->realisasi();

        return ['hitung' => $this->hitung, 'galat' => $this->galat, 'peringatan' => $this->peringatan];
    }

    private function prodi(): void
    {
        foreach ($this->baca('prodi.csv') as $no => $baris) {
            $this->proses('prodi', $no, 'prodi.csv', function () use ($baris) {
                $data = $this->validasi($baris, [
                    'kode' => 'required|string|max:20',
                    'nama' => 'required|string|max:255',
                    'jenjang' => ['required', Rule::enum(JenjangProdi::class)],
                    'aktif' => 'nullable|in:0,1',
                ]);

                $model = Prodi::firstOrNew(['kode' => $data['kode']]);
                $baru = ! $model->exists;
                $model->fill([
                    'nama' => $data['nama'],
                    'jenjang' => $data['jenjang'],
                    'aktif' => ($data['aktif'] ?? '1') === '1',
                ])->save();

                return $baru;
            });
        }
    }

    private function mitra(): void
    {
        foreach ($this->baca('mitra.csv') as $no => $baris) {
            $this->proses('mitra', $no, 'mitra.csv', function () use ($baris) {
                $baris['negara'] = filled($baris['negara'] ?? null) ? $baris['negara'] : 'Indonesia';

                $data = $this->validasi($baris, [
                    'nama' => 'required|string|max:255',
                    'jenis' => ['required', Rule::enum(JenisMitra::class)],
                    'negara' => 'required|string|max:255',
                    'website' => 'nullable|url|max:255',
                    'kontak_email' => 'nullable|email|max:255',
                    'status_legal' => [Rule::requiredIf(strcasecmp($baris['negara'], 'Indonesia') !== 0), 'nullable', 'string', 'max:255'],
                ]);

                $model = Mitra::withTrashed()->firstOrNew(['nama' => $data['nama'], 'negara' => $data['negara']]);

                if ($model->trashed()) {
                    $this->peringatan[] = "mitra.csv baris {$this->nomor($baris)}: mitra \"{$data['nama']}\" sudah dihapus, dilewati.";

                    return null;
                }

                $baru = ! $model->exists;
                $model->fill(collect($baris)->only([
                    'jenis', 'provinsi', 'kabupaten_kota', 'alamat', 'website', 'kontak_nama',
                    'kontak_jabatan', 'kontak_email', 'kontak_telepon', 'status_legal', 'catatan',
                ])->map(fn ($v) => blank($v) ? null : $v)->all())->save();

                return $baru;
            });
        }
    }

    private function kerjaSama(): void
    {
        $induk = [];

        foreach ($this->baca('kerja_sama.csv') as $no => $baris) {
            $this->proses('kerja_sama', $no, 'kerja_sama.csv', function () use ($baris, &$induk) {
                $data = $this->validasi($baris, [
                    'kode_impor' => 'required|string|max:255',
                    'jenis_dokumen' => ['required', Rule::enum(JenisDokumen::class)],
                    'judul' => 'required|string|max:255',
                    'ruang_lingkup' => 'required|string',
                    'bidang' => ['required', Rule::enum(BidangKerjaSama::class)],
                    'tingkat' => ['required', Rule::enum(TingkatKerjaSama::class)],
                    'pihak_penandatangan_unsil' => ['required', Rule::enum(PihakPenandatangan::class)],
                    'tanggal_tanda_tangan' => 'required|date_format:Y-m-d',
                    'tanggal_mulai' => 'required|date_format:Y-m-d',
                    'tanggal_berakhir' => 'required|date_format:Y-m-d|after:tanggal_mulai',
                    'memuat_hki_aset' => 'nullable|in:0,1',
                    'perlu_persetujuan_dirjen' => 'nullable|in:0,1',
                    'sudah_dilaporkan_pddikti' => 'nullable|in:0,1',
                    'mitra' => 'required|string',
                ]);

                $mitraIds = $this->cari(Mitra::class, 'nama', $this->daftar($data['mitra']), 'mitra');
                $prodiIds = $this->cari(Prodi::class, 'kode', $this->daftar($baris['prodi'] ?? ''), 'prodi');
                $inisiator = $this->cari(Prodi::class, 'kode', $this->daftar($baris['prodi_penginisiasi'] ?? ''), 'prodi penginisiasi');
                $bentukIds = $this->cari(BentukKerjaSama::class, 'kode', $this->daftar($baris['bentuk'] ?? ''), 'bentuk');

                $model = KerjaSama::withTrashed()->firstOrNew(['kode_impor' => $data['kode_impor']]);

                if ($model->trashed()) {
                    $this->peringatan[] = "kerja_sama.csv baris {$this->nomor($baris)}: {$data['kode_impor']} sudah dihapus, dilewati.";

                    return null;
                }

                $baru = ! $model->exists;
                $model->fill([
                    'jenis_dokumen' => $data['jenis_dokumen'],
                    'nomor_dokumen_unsil' => $this->nullable($baris, 'nomor_dokumen_unsil'),
                    'nomor_dokumen_mitra' => $this->nullable($baris, 'nomor_dokumen_mitra'),
                    'judul' => $data['judul'],
                    'ruang_lingkup' => $data['ruang_lingkup'],
                    'bidang' => $data['bidang'],
                    'tingkat' => $data['tingkat'],
                    'pihak_penandatangan_unsil' => $data['pihak_penandatangan_unsil'],
                    'nama_penandatangan_unsil' => $this->nullable($baris, 'nama_penandatangan_unsil'),
                    'nama_penandatangan_mitra' => $this->nullable($baris, 'nama_penandatangan_mitra'),
                    'jabatan_penandatangan_mitra' => $this->nullable($baris, 'jabatan_penandatangan_mitra'),
                    'tanggal_tanda_tangan' => $data['tanggal_tanda_tangan'],
                    'tanggal_mulai' => $data['tanggal_mulai'],
                    'tanggal_berakhir' => $data['tanggal_berakhir'],
                    'memuat_hki_aset' => ($data['memuat_hki_aset'] ?? '0') === '1',
                    'perlu_persetujuan_dirjen' => ($data['perlu_persetujuan_dirjen'] ?? '0') === '1',
                    'sudah_dilaporkan_pddikti' => ($data['sudah_dilaporkan_pddikti'] ?? '0') === '1',
                ]);

                if ($baru) {
                    $model->dibuat_oleh = $this->pembuat->id;
                }

                $model->save();

                $model->mitra()->sync($mitraIds);
                $model->bentuk()->sync($bentukIds);
                $model->prodi()->sync(collect($prodiIds)->merge($inisiator)->unique()->mapWithKeys(
                    fn ($id) => [$id => ['penginisiasi' => in_array($id, $inisiator, true)]]
                )->all());

                $this->salinBerkas($model, $baris);
                $model->terapkanAturanMitraAsing();

                if (filled($baris['kode_impor_induk'] ?? null)) {
                    $induk[$model->id] = trim($baris['kode_impor_induk']);
                }

                return $baru;
            });
        }

        foreach ($induk as $anakId => $kodeInduk) {
            $parent = KerjaSama::where('kode_impor', $kodeInduk)->first();

            if (! $parent) {
                $this->galat[] = "kerja_sama.csv: induk \"{$kodeInduk}\" tidak ditemukan.";

                continue;
            }

            KerjaSama::whereKey($anakId)->update(['induk_id' => $parent->id]);
        }
    }

    private function realisasi(): void
    {
        foreach ($this->baca('realisasi_kegiatan.csv') as $no => $baris) {
            $this->proses('realisasi_kegiatan', $no, 'realisasi_kegiatan.csv', function () use ($baris) {
                $data = $this->validasi($baris, [
                    'kode_impor_kerja_sama' => 'required|string',
                    'judul_kegiatan' => 'required|string|max:255',
                    'dharma' => ['nullable', Rule::enum(Dharma::class)],
                    'tanggal_mulai' => 'required|date_format:Y-m-d',
                    'tanggal_selesai' => 'nullable|date_format:Y-m-d|after_or_equal:tanggal_mulai',
                    'manfaat_bagi_prodi' => 'required|string',
                    'jumlah_mahasiswa' => 'nullable|integer|min:0|max:65535',
                    'jumlah_dosen' => 'nullable|integer|min:0|max:65535',
                    'status_verifikasi' => ['nullable', Rule::enum(StatusVerifikasi::class)],
                ]);

                $ks = KerjaSama::where('kode_impor', $data['kode_impor_kerja_sama'])->first()
                    ?? throw new RuntimeException("kerja sama \"{$data['kode_impor_kerja_sama']}\" tidak ditemukan");

                $bentuk = filled($baris['bentuk'] ?? null)
                    ? BentukKerjaSama::firstWhere('kode', trim($baris['bentuk'])) ?? throw new RuntimeException("bentuk \"{$baris['bentuk']}\" tidak ditemukan")
                    : null;

                $dharma = $data['dharma'] ?? $bentuk?->dharma_default?->value
                    ?? throw new RuntimeException('dharma kosong dan bentuk tidak punya dharma bawaan');

                $prodiIds = $this->cari(Prodi::class, 'kode', $this->daftar($baris['prodi'] ?? ''), 'prodi');

                $model = RealisasiKegiatan::withTrashed()->firstOrNew([
                    'kerja_sama_id' => $ks->id,
                    'judul_kegiatan' => $data['judul_kegiatan'],
                    'tanggal_mulai' => $data['tanggal_mulai'],
                ]);

                if ($model->trashed()) {
                    $this->peringatan[] = "realisasi_kegiatan.csv baris {$this->nomor($baris)}: \"{$data['judul_kegiatan']}\" sudah dihapus, dilewati.";

                    return null;
                }

                $baru = ! $model->exists;
                $model->fill([
                    'deskripsi' => $this->nullable($baris, 'deskripsi'),
                    'dharma' => $dharma,
                    'bentuk_kerja_sama_id' => $bentuk?->id,
                    'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                    'manfaat_bagi_prodi' => $data['manfaat_bagi_prodi'],
                    'luaran' => $this->nullable($baris, 'luaran'),
                    'jumlah_mahasiswa' => (int) ($data['jumlah_mahasiswa'] ?? 0),
                    'jumlah_dosen' => (int) ($data['jumlah_dosen'] ?? 0),
                    'status_verifikasi' => $data['status_verifikasi'] ?? StatusVerifikasi::Draf->value,
                ]);

                if ($baru) {
                    $model->dibuat_oleh = $this->pembuat->id;
                }

                $model->save();
                $model->prodi()->sync($prodiIds);

                return $baru;
            });
        }
    }

    /** Salin berkas dari <folder>/berkas/ ke disk privat; hanya PDF. */
    private function salinBerkas(KerjaSama $model, array $baris): void
    {
        $nama = trim($baris['berkas_dokumen'] ?? '');

        if ($nama === '') {
            return;
        }

        $sumber = $this->folder.'/berkas/'.basename($nama);

        if (! is_file($sumber)) {
            $this->peringatan[] = "kerja_sama.csv: berkas \"{$nama}\" tidak ditemukan di folder berkas/, dilewati.";

            return;
        }

        if (strtolower(pathinfo($sumber, PATHINFO_EXTENSION)) !== 'pdf') {
            $this->peringatan[] = "kerja_sama.csv: berkas \"{$nama}\" bukan PDF, dilewati.";

            return;
        }

        $tujuan = 'kerja-sama/impor-'.$model->kode_impor.'.pdf';
        Storage::disk('local')->put($tujuan, file_get_contents($sumber));
        $model->update(['berkas_dokumen' => $tujuan]);
    }

    /** @return array<int, array<string, string>> baris CSV berindeks nomor baris berkas (header = 1) */
    private function baca(string $berkas): array
    {
        $path = $this->folder.'/'.$berkas;

        if (! is_file($path)) {
            $this->peringatan[] = "{$berkas} tidak ada, dilewati.";

            return [];
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, escape: '');

        if ($header === false) {
            fclose($handle);

            return [];
        }

        $header = array_map(fn ($h) => trim(ltrim((string) $h, "\xEF\xBB\xBF")), $header);
        $baris = [];
        $no = 1;

        while (($kolom = fgetcsv($handle, escape: '')) !== false) {
            $no++;

            if ($kolom === [null] || blank(implode('', $kolom))) {
                continue;
            }

            if (str_starts_with(strtoupper(trim((string) ($kolom[0] ?? ''))), 'CONTOH')) {
                $this->penghitung(pathinfo($berkas, PATHINFO_FILENAME))['dilewati']++;

                continue;
            }

            $kolom = array_pad($kolom, count($header), '');
            $item = array_combine($header, array_slice($kolom, 0, count($header)));
            $item['__baris'] = $no;
            $baris[$no] = $item;
        }

        fclose($handle);

        return $baris;
    }

    private function proses(string $entitas, int $no, string $berkas, callable $aksi): void
    {
        $this->penghitung($entitas);

        try {
            $baru = $aksi();

            if ($baru === null) {
                $this->hitung[$entitas]['dilewati']++;
            } else {
                $this->hitung[$entitas][$baru ? 'dibuat' : 'diperbarui']++;
            }
        } catch (\Throwable $e) {
            $this->hitung[$entitas]['dilewati']++;
            $this->galat[] = "{$berkas} baris {$no}: ".$e->getMessage();
        }
    }

    /** @return array{dibuat: int, diperbarui: int, dilewati: int} */
    private function &penghitung(string $entitas): array
    {
        $this->hitung[$entitas] ??= ['dibuat' => 0, 'diperbarui' => 0, 'dilewati' => 0];

        return $this->hitung[$entitas];
    }

    private function validasi(array $baris, array $aturan): array
    {
        $validator = Validator::make($baris, $aturan);

        if ($validator->fails()) {
            throw new RuntimeException($validator->errors()->first());
        }

        // Sel CSV kosong dianggap null agar `??` bekerja seperti yang diharapkan.
        return array_map(fn ($nilai) => $nilai === '' ? null : $nilai, $baris);
    }

    /** @return list<string> */
    private function daftar(string $nilai): array
    {
        return collect(explode('|', $nilai))->map(fn ($v) => trim($v))->filter()->values()->all();
    }

    /** @return list<int> id berurutan sesuai daftar; gagal bila ada yang tidak ditemukan */
    private function cari(string $model, string $kolom, array $nilai, string $label): array
    {
        $ketemu = $model::whereIn($kolom, $nilai)->pluck('id', $kolom);
        $hilang = collect($nilai)->reject(fn ($n) => $ketemu->has($n));

        if ($hilang->isNotEmpty()) {
            throw new RuntimeException("{$label} tidak ditemukan: ".$hilang->implode(', '));
        }

        return collect($nilai)->map(fn ($n) => (int) $ketemu[$n])->all();
    }

    private function nullable(array $baris, string $kunci): ?string
    {
        return filled($baris[$kunci] ?? null) ? trim($baris[$kunci]) : null;
    }

    private function nomor(array $baris): int
    {
        return (int) ($baris['__baris'] ?? 0);
    }
}
