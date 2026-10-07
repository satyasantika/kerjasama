# Sistem Kerja Sama FKIP

Aplikasi pengelolaan dokumen dan data kerja sama (MoU/MoA/PKS/IA) Fakultas Keguruan
dan Ilmu Pendidikan Universitas Siliwangi dengan mitra — sekolah, pemerintah daerah,
perguruan tinggi lain, dunia usaha/industri, dan lembaga lain, dalam dan luar negeri.

## Fungsi utama

1. Menyimpan dokumen perjanjian kerja sama secara terpusat dan terstruktur, dengan
   relasi induk–anak (MoU → MoA/PKS → IA).
2. Memantau masa berlaku dokumen dan mengingatkan sebelum jatuh tempo.
3. Mencatat realisasi kegiatan dari setiap kerja sama beserta buktinya, lewat alur
   pengajuan dan verifikasi.
4. Menyediakan data siap pakai untuk akreditasi **LAMDIK IAPSK 3.0** (elemen *Kerja
   Sama Tridharma PT*) dan laporan kinerja (IKU).

## Stack

| Aspek | Keterangan |
|---|---|
| Framework | Laravel 13 |
| Panel admin | Filament 5 (Livewire 4) di `/admin` |
| Database | MariaDB, driver `mariadb` |
| Role & izin | `spatie/laravel-permission` |
| Ekspor Excel | `openspout/openspout` |
| PDF | `barryvdh/laravel-dompdf` |
| Runtime | Docker (`kerjasama-php`, `kerjasama-nginx`) |
| Bahasa domain | Indonesia (tabel, kolom, model, label UI) |
| Zona waktu / lokal | `Asia/Jakarta`, `id`, format tanggal `d/m/Y` |

## Peran pengguna

| Peran | Siapa | Hak akses |
|---|---|---|
| Super admin | Pengembang/operator TI | Semua, termasuk kelola pengguna & peran |
| Admin fakultas | Staf urusan kerja sama fakultas | CRUD semua data; kelola master mitra, prodi, bentuk kerja sama; verifikasi realisasi |
| Admin prodi | Operator/GKM prodi | Lihat semua kerja sama; tambah/ubah kerja sama & realisasi yang melibatkan prodinya; tidak bisa hapus |
| Pimpinan | Dekan, wakil dekan, kaprodi | Hanya lihat: dasbor, daftar, unduh dokumen, ekspor laporan |

Pembatasan akses `admin_prodi` dan `pimpinan` ke data prodinya ditegakkan lewat
Policy Laravel, bukan hanya menyembunyikan tombol di antarmuka.

## Istilah domain

| Istilah | Arti dalam sistem ini |
|---|---|
| MoU | Nota kesepahaman — payung, biasanya tingkat universitas |
| MoA | Perjanjian kerja sama tingkat fakultas/unit, bisa di bawah MoU |
| PKS | Perjanjian Kerja Sama / dokumen pelaksanaan — rincian teknis |
| IA | *Implementation Arrangement* — rincian pelaksanaan (umum untuk mitra luar negeri) |
| Realisasi kegiatan | Kegiatan nyata atas dasar sebuah dokumen (pertukaran, PLP/magang, penelitian bersama, PkM, seminar, dll.) — dasar perhitungan akreditasi |
| Tingkat | Lokal, nasional, internasional |
| Dharma | Pendidikan, penelitian, PkM |

## Status kerja sama (dihitung, bukan disimpan)

| Status | Aturan |
|---|---|
| Dibatalkan / dihentikan | Ditetapkan manual |
| Belum berlaku | Hari ini sebelum tanggal mulai |
| Kedaluwarsa | Hari ini melewati tanggal berakhir |
| Akan berakhir | Sisa ≤ 90 hari |
| Aktif | Selain kondisi di atas |

## Menjalankan secara lokal

```bash
docker compose up -d kerjasama-php kerjasama-nginx
docker exec kerjasama-php php artisan migrate:fresh --seed
docker exec kerjasama-php php artisan test
```

Aplikasi berjalan di `http://localhost:8025/admin`. Variabel lingkungan penting ada
di `.env.example` — isi `SUPER_ADMIN_EMAIL`/`SUPER_ADMIN_PASSWORD` sebelum seeding di
luar environment `local`/`testing`.

## Impor data awal

```bash
docker exec kerjasama-php php artisan kerjasama:impor storage/app/impor
```

Perintah bersifat idempoten — menjalankannya ulang dengan data yang sama tidak
menggandakan baris. Format CSV mengikuti templat yang disiapkan terpisah (tidak
disertakan di repo).

## Pengingat jatuh tempo

Dijalankan terjadwal (`php artisan schedule:run` via cron) setiap hari pukul 07:00,
dikirim lewat notifikasi database Filament. Ambang hari diatur lewat
`KERJASAMA_AMBANG_AKAN_BERAKHIR` (`.env`).

## Ekspor laporan akreditasi

Ekspor Excel "Tabel Kerja Sama Tridharma" per prodi dan rentang tahun akademik,
tiga lembar (Pendidikan, Penelitian, PkM), berisi kolom: lembaga mitra, tingkat,
judul kegiatan, manfaat bagi prodi, waktu & durasi, bukti kerja sama, tahun berakhir.

## Struktur data (ringkas)

Primary key seluruh tabel domain memakai UUID versi 7 (`HasUuids`). Tabel utama:
`prodi`, `prodi_ndtps`, `mitra`, `bentuk_kerja_sama`, `kerja_sama` (dengan pivot
`kerja_sama_mitra`, `kerja_sama_prodi`, `kerja_sama_bentuk`), `realisasi_kegiatan`
(dengan pivot `realisasi_kegiatan_prodi`), `berkas_bukti`, `evaluasi_kerja_sama`,
`pengingat_terkirim`.

## Pengujian

```bash
docker exec kerjasama-php php artisan test
```

Mencakup pengujian Policy otorisasi per peran, perhitungan status kerja sama,
alur verifikasi realisasi, impor CSV idempoten, dan otorisasi unduh berkas.
