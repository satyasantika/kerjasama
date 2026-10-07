<?php

namespace App\Actions\Pengguna;

use App\Enums\Peran;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use RuntimeException;

/**
 * Input massal pengguna dari CSV/XLSX (berkas diproses lalu dibuang, tidak disimpan).
 * Semua-atau-tidak-sama-sekali: bila ada satu baris tidak sah, tidak ada akun dibuat.
 * Kolom: nama, email, peran, kode_prodi, kata_sandi (opsional; kosong = dibangkitkan).
 */
class ImporPengguna
{
    public const KOLOM = ['nama', 'email', 'peran', 'kode_prodi', 'kata_sandi'];

    public const MAKS_BARIS = 500;

    /** Peran yang boleh dibuat massal; Super Admin hanya lewat menu satu per satu. */
    private const PERAN_BOLEH = [Peran::AdminFakultas, Peran::AdminProdi, Peran::Pimpinan];

    public function __construct(private readonly SimpanPengguna $simpan) {}

    /**
     * @return array{galat: list<string>, hasil: list<array{nama: string, email: string, peran: string, prodi: string, kata_sandi: string}>}
     */
    public function handle(string $path, string $ekstensi, User $pelaku): array
    {
        $baris = $this->baca($path, strtolower($ekstensi));

        if ($baris === []) {
            return ['galat' => ['Berkas kosong atau tidak memuat baris data.'], 'hasil' => []];
        }

        if (count($baris) > self::MAKS_BARIS) {
            return ['galat' => ['Maksimal '.self::MAKS_BARIS.' baris per impor.'], 'hasil' => []];
        }

        [$siap, $galat] = $this->validasi($baris);

        if ($galat !== []) {
            return ['galat' => $galat, 'hasil' => []];
        }

        $hasil = DB::transaction(function () use ($siap, $pelaku) {
            $hasil = [];

            foreach ($siap as $item) {
                $this->simpan->handle(null, [
                    'name' => $item['nama'],
                    'email' => $item['email'],
                    'password' => $item['kata_sandi'],
                    'peran' => $item['peran'],
                    'prodi_id' => $item['prodi_id'],
                    'aktif' => true,
                ], $pelaku);

                $hasil[] = [
                    'nama' => $item['nama'],
                    'email' => $item['email'],
                    'peran' => $item['peran']->label(),
                    'prodi' => $item['prodi_nama'],
                    'kata_sandi' => $item['dibangkitkan'] ? $item['kata_sandi'] : '(sesuai berkas)',
                ];
            }

            return $hasil;
        });

        return ['galat' => [], 'hasil' => $hasil];
    }

    /**
     * @param  list<array{__baris: int}&array<string, string>>  $baris
     * @return array{0: list<array<string, mixed>>, 1: list<string>}
     */
    private function validasi(array $baris): array
    {
        $prodi = Prodi::where('aktif', true)->get()->keyBy(fn (Prodi $p) => strtoupper($p->kode));
        $emailDiBerkas = [];
        $siap = [];
        $galat = [];

        foreach ($baris as $item) {
            $no = $item['__baris'];
            $peran = $this->peran($item['peran']);
            $email = Str::lower(trim($item['email']));
            $kodeProdi = strtoupper(trim($item['kode_prodi']));
            $sandi = trim($item['kata_sandi']);

            $pesan = Validator::make(
                ['nama' => trim($item['nama']), 'email' => $email, 'kata_sandi' => $sandi === '' ? null : $sandi],
                [
                    'nama' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                    'kata_sandi' => ['nullable', 'string', 'min:8', 'max:255'],
                ],
                [],
                ['nama' => 'nama', 'email' => 'email', 'kata_sandi' => 'kata sandi'],
            )->errors()->all();

            if (isset($emailDiBerkas[$email]) && $email !== '') {
                $pesan[] = "email dobel dengan baris {$emailDiBerkas[$email]}.";
            }
            $emailDiBerkas[$email] ??= $no;

            if ($peran === null) {
                $pesan[] = 'peran tidak dikenali (pakai: admin_fakultas, admin_prodi, atau pimpinan).';
            }

            $prodiModel = $kodeProdi !== '' ? $prodi->get($kodeProdi) : null;

            if ($kodeProdi !== '' && $prodiModel === null) {
                $pesan[] = "kode prodi \"{$kodeProdi}\" tidak ditemukan atau tidak aktif.";
            }

            if ($peran === Peran::AdminProdi && $kodeProdi === '') {
                $pesan[] = 'Admin Prodi wajib memiliki kode_prodi.';
            }

            if ($pesan !== []) {
                foreach ($pesan as $p) {
                    $galat[] = "Baris {$no}: {$p}";
                }

                continue;
            }

            $dibangkitkan = $sandi === '';
            $siap[] = [
                'nama' => trim($item['nama']),
                'email' => $email,
                'peran' => $peran,
                'prodi_id' => in_array($peran, [Peran::AdminProdi, Peran::Pimpinan], true) ? $prodiModel?->id : null,
                'prodi_nama' => in_array($peran, [Peran::AdminProdi, Peran::Pimpinan], true) ? ($prodiModel?->nama ?? '') : '',
                'kata_sandi' => $dibangkitkan ? Str::password(12, symbols: false) : $sandi,
                'dibangkitkan' => $dibangkitkan,
            ];
        }

        return [$siap, $galat];
    }

    private function peran(string $nilai): ?Peran
    {
        $kunci = Str::of($nilai)->trim()->lower()->replace([' ', '-'], '_')->toString();

        foreach (self::PERAN_BOLEH as $peran) {
            if ($kunci === $peran->value) {
                return $peran;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> baris data berkunci nama kolom (+ __baris), baris CONTOH dan kosong dilewati. */
    private function baca(string $path, string $ekstensi): array
    {
        $mentah = match ($ekstensi) {
            'xlsx' => $this->bacaXlsx($path),
            'csv', 'txt' => $this->bacaCsv($path),
            default => throw new RuntimeException('Format berkas harus CSV atau XLSX.'),
        };

        if ($mentah === []) {
            return [];
        }

        $header = array_map(fn ($h) => Str::of((string) $h)->replace("\xEF\xBB\xBF", '')->trim()->lower()->replace([' ', '-'], '_')->toString(), array_shift($mentah));
        $hilang = array_diff(self::KOLOM, $header);

        if (array_intersect(['nama', 'email', 'peran'], $hilang) !== []) {
            throw new RuntimeException('Kolom wajib tidak ditemukan: '.implode(', ', array_intersect(['nama', 'email', 'peran'], $hilang)).'.');
        }

        $baris = [];

        foreach ($mentah as $i => $kolom) {
            $kolom = array_map(fn ($v) => trim((string) $v), $kolom);

            if (blank(implode('', $kolom)) || str_starts_with(strtoupper($kolom[0] ?? ''), 'CONTOH')) {
                continue;
            }

            $kolom = array_pad($kolom, count($header), '');
            $item = array_combine($header, array_slice($kolom, 0, count($header)));
            $item = array_merge(array_fill_keys(self::KOLOM, ''), $item);
            $item['__baris'] = $i + 2;
            $baris[] = $item;
        }

        return $baris;
    }

    /** @return list<list<string>> */
    private function bacaCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $pertama = (string) fgets($handle);
        rewind($handle);
        $pemisah = substr_count($pertama, ';') > substr_count($pertama, ',') ? ';' : ',';
        $hasil = [];

        while (($kolom = fgetcsv($handle, separator: $pemisah, escape: '')) !== false) {
            $hasil[] = $kolom;
        }

        fclose($handle);

        return $hasil;
    }

    /** @return list<list<string>> */
    private function bacaXlsx(string $path): array
    {
        $reader = new XlsxReader;
        $reader->open($path);
        $hasil = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $hasil[] = array_map(fn ($c) => (string) $c, $row->toArray());
            }

            break;
        }

        $reader->close();

        return $hasil;
    }
}
