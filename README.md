# Sistem Kerja Sama FKIP

> Status: **Siap dibangun (spesifikasi v1)**. Belum ada kode. Dokumen ini adalah
> spesifikasi untuk vibecoding. Aturan kerja untuk agen AI ada di [`CLAUDE.md`](CLAUDE.md),
> rujukan regulasi & rumus akreditasi di [`docs/rujukan.md`](docs/rujukan.md),
> templat impor data awal di [`contoh-data/`](contoh-data/).
>
> Hal yang ditandai **⚠ ASUMSI** adalah keputusan default yang dibuat saat menyusun
> spesifikasi — koreksi di sini sebelum/selama membangun.

## 1. Deskripsi

Sistem pengelolaan dokumen dan data kerja sama (MoU/MoA/PKS/IA) FKIP Universitas
Siliwangi dengan mitra — sekolah, pemerintah daerah, perguruan tinggi lain, dunia
usaha/industri, dan lembaga lain, dalam dan luar negeri.

## 2. Tujuan

1. Menyimpan dokumen perjanjian kerja sama secara terpusat dan terstruktur.
2. Memantau masa berlaku dan mengingatkan sebelum jatuh tempo (perpanjangan/evaluasi).
3. Mencatat realisasi kegiatan dari setiap kerja sama beserta buktinya.
4. Menyediakan data siap pakai untuk akreditasi **LAMDIK IAPSK 3.0** (elemen *Kerja Sama
   Tridharma PT*) dan laporan kinerja (IKU).

## 3. Keputusan teknis

| Aspek | Keputusan |
|---|---|
| Framework | **Laravel 13** |
| Panel admin / UI | **Filament 5** (dokumentasi `filamentphp.com/docs/5.x`) |
| Livewire | **Livewire 4** (dibawa Filament 5) |
| CSS | Tailwind CSS 4 (bawaan Filament) |
| Database | **MariaDB 11.4 LTS** (image Docker `mariadb:11.4`, driver Laravel `mariadb`) |
| Role & izin | `spatie/laravel-permission` |
| Ekspor Excel | `maatwebsite/excel` (atau `openspout`, pilih yang kompatibel Laravel 13) |
| Notifikasi | Laravel Task Scheduling + Filament database notifications (+ e-mail, fase 2) |
| Berkas | Laravel Storage disk `local` (privat), unduh lewat route berotorisasi |
| Repo kode | `~/code/kerjasama` di WSL — **⚠ ASUMSI**, mengikuti pola `alias` |
| Runtime | Docker, pola sama dengan repo `alias` (container `kerjasama-php`, `kerjasama-nginx`, `kerjasama-mariadb`) |
| Port lokal | **8019** — **⚠ ASUMSI** (alias = 8018); cek bentrok dengan sistem lain |
| Bahasa | UI & nama domain (tabel, kolom, model) Bahasa Indonesia |
| Zona waktu / lokal | `Asia/Jakarta`, `id`, format tanggal `d/m/Y` |

Alasan Filament 5 (bukan 4 seperti `alias`): proyek baru tanpa kode lama; v5 identik
fiturnya dengan v4, hanya menambah dukungan Livewire 4. Konsekuensi: potongan kode
Livewire dari `alias` tidak selalu bisa disalin mentah.

## 4. Peran pengguna

| Role (Spatie) | Siapa | Hak akses |
|---|---|---|
| `super_admin` | Pengembang/operator TI | Semua, termasuk kelola user & role |
| `admin_fakultas` | Staf urusan kerja sama fakultas | CRUD semua data; kelola master mitra, prodi, bentuk kerja sama; verifikasi realisasi |
| `admin_prodi` | Operator/GKM prodi | Lihat semua kerja sama; tambah/ubah kerja sama & realisasi **yang melibatkan prodinya**; tidak bisa hapus |
| `pimpinan` | Dekan, wakil dekan, kaprodi | Hanya lihat: dashboard, daftar, unduh dokumen, ekspor laporan |

Aturan: user `admin_prodi` dan `pimpinan` (kaprodi) punya `prodi_id`; data yang bisa
mereka ubah dibatasi oleh relasi `kerja_sama_prodi`. Atur lewat Policy Laravel, bukan
hanya menyembunyikan tombol.

## 5. Istilah domain

| Istilah | Arti dalam sistem ini |
|---|---|
| **MoU** | Nota kesepahaman — payung, biasanya tingkat universitas (Rektor). |
| **MoA** | Perjanjian kerja sama tingkat fakultas/unit, bisa di bawah MoU. |
| **PKS** | Perjanjian Kerja Sama / dokumen pelaksanaan — rincian teknis, di bawah MoU/MoA. |
| **IA** | *Implementation Arrangement* — rincian pelaksanaan (umum untuk mitra luar negeri). |
| **Realisasi kegiatan** | Kegiatan nyata yang dijalankan atas dasar sebuah dokumen (pertukaran, PLP/magang, penelitian bersama, PkM, seminar, dll.). Inilah yang dihitung untuk akreditasi. |
| **Tingkat** | `lokal` (wilayah/lokal), `nasional`, `internasional` — istilah LAMDIK. |
| **Dharma** | `pendidikan`, `penelitian`, `pkm` — dasar N1/N2/N3 LAMDIK. |
| **TS** | Tahun Sekarang (tahun akademik penilaian). "3 tahun terakhir" = TS-2 s.d. TS. |

Hierarki dokumen: `MoU → MoA/PKS → IA` lewat kolom `induk_id` (self-reference). Dokumen
boleh berdiri sendiri tanpa induk.

## 6. Skema database

Konvensi: nama tabel Bahasa Indonesia **tanpa jamak Inggris** — selalu set `$table`
di model (Laravel akan menebak `mitras`, `kerja_samas`, dst. kalau tidak).

```
users                     (bawaan Laravel) + prodi_id FK nullable
roles, permissions, ...   (Spatie)

prodi (
  id, kode VARCHAR(20) unique, nama, jenjang ENUM('S1','S2','S3','PPG','Profesi'),
  aktif BOOL default true, timestamps
)

prodi_ndtps (                       -- input manual untuk rumus LAMDIK
  id, prodi_id FK, tahun_ts YEAR, ndtps SMALLINT,  unique(prodi_id, tahun_ts)
)

mitra (
  id, nama, jenis ENUM('perguruan_tinggi','sekolah','pemerintah','dudi',
                       'organisasi_profesi','lembaga_lain'),
  negara VARCHAR default 'Indonesia', provinsi NULL, kabupaten_kota NULL,
  alamat TEXT NULL, website NULL,
  kontak_nama NULL, kontak_jabatan NULL, kontak_email NULL, kontak_telepon NULL,
  status_legal NULL,      -- akreditasi PT luar negeri / registrasi badan usaha (Permendikbud 14/2014 Ps. 48 ay. 3)
  catatan TEXT NULL, timestamps, softDeletes,
  unique(nama, negara)                           -- kunci idempoten impor CSV
)

bentuk_kerja_sama (                 -- master, di-seed dari Permendikbud 14/2014 (lihat docs/rujukan.md)
  id, kode VARCHAR unique, nama, bidang ENUM('akademik','non_akademik'),
  ruang ENUM('antar_pt','dudi_pihak_lain'), dharma_default ENUM('pendidikan','penelitian','pkm') NULL,
  pasal_rujukan VARCHAR, aktif BOOL
)

kerja_sama (                        -- satu baris = satu dokumen perjanjian
  id, induk_id FK kerja_sama NULL,
  kode_impor VARCHAR unique NULL,               -- kunci idempoten impor CSV
  jenis_dokumen ENUM('MoU','MoA','PKS','IA'),
  nomor_dokumen_unsil NULL, nomor_dokumen_mitra NULL,
  judul,
  ruang_lingkup TEXT,
  bidang ENUM('akademik','non_akademik','keduanya'),
  tingkat ENUM('lokal','nasional','internasional'),
  pihak_penandatangan_unsil ENUM('rektor','dekan','wakil_dekan','kaprodi','lainnya'),
  nama_penandatangan_unsil NULL, nama_penandatangan_mitra NULL, jabatan_penandatangan_mitra NULL,
  tanggal_tanda_tangan DATE,
  tanggal_mulai DATE, tanggal_berakhir DATE,      -- WAJIB (Ps. 47 ay. 2 huruf e)
  status_manual ENUM('dibatalkan','dihentikan') NULL,   -- status lain dihitung, lihat §7
  memuat_hki_aset BOOL default false,             -- Ps. 47 ay. 3
  perlu_persetujuan_dirjen BOOL default false,    -- Ps. 49 ay. 1
  sudah_dilaporkan_pddikti BOOL default false,    -- Ps. 49 ay. 3
  berkas_dokumen VARCHAR NULL,                    -- path PDF dokumen bertanda tangan
  berkas_dokumen_asing VARCHAR NULL,              -- versi bahasa asing (Ps. 47 ay. 4)
  dibuat_oleh FK users, timestamps, softDeletes
)

kerja_sama_mitra   (kerja_sama_id, mitra_id)                         -- bisa multipihak
kerja_sama_prodi   (kerja_sama_id, prodi_id, penginisiasi BOOL)      -- prodi terlibat
kerja_sama_bentuk  (kerja_sama_id, bentuk_kerja_sama_id)

realisasi_kegiatan (
  id, kerja_sama_id FK,
  judul_kegiatan, deskripsi TEXT NULL,
  dharma ENUM('pendidikan','penelitian','pkm'),
  bentuk_kerja_sama_id FK NULL,
  tanggal_mulai DATE, tanggal_selesai DATE NULL,
  manfaat_bagi_prodi TEXT,                        -- kolom wajib tabel bukti akreditasi
  luaran TEXT NULL,                               -- produk/hasil (relevan IKU 5)
  jumlah_mahasiswa SMALLINT default 0, jumlah_dosen SMALLINT default 0,
  status_verifikasi ENUM('draf','diajukan','terverifikasi','ditolak') default 'draf',
  diverifikasi_oleh FK users NULL, diverifikasi_pada DATETIME NULL, catatan_verifikasi NULL,
  dibuat_oleh FK users, timestamps, softDeletes
)
realisasi_kegiatan_prodi (realisasi_kegiatan_id, prodi_id)

berkas_bukti (                      -- lampiran bukti realisasi (laporan, foto, daftar hadir)
  id, realisasi_kegiatan_id FK, nama_berkas, path, jenis ENUM('laporan','daftar_hadir',
  'foto','surat_tugas','lainnya'), ukuran_kb, timestamps
)

evaluasi_kerja_sama (               -- fase 2; dasar indikator (b) LAMDIK
  id, kerja_sama_id FK, tahun_ts YEAR, skor_keefektifan TINYINT (1-4),
  analisis TEXT, rekomendasi ENUM('lanjutkan','perpanjang','revisi','hentikan'),
  dinilai_oleh FK users, timestamps
)

pengingat_terkirim (                -- cegah notifikasi ganda
  id, kerja_sama_id FK, ambang_hari SMALLINT, dikirim_pada DATETIME,
  unique(kerja_sama_id, ambang_hari)
)
```

Validasi penting:
- `tanggal_berakhir` > `tanggal_mulai`; `tanggal_mulai` ≥ `tanggal_tanda_tangan` tidak wajib (boleh berlaku surut — beri peringatan saja).
- Dokumen anak (`induk_id` terisi) tidak boleh berakhir setelah induknya — peringatan, bukan blokir.
- Jika ada mitra dengan `negara` ≠ Indonesia: `tingkat` otomatis `internasional`, `perlu_persetujuan_dirjen` otomatis `true`, dan form menampilkan isian `berkas_dokumen_asing`.
- Unggahan hanya PDF, maks 10 MB per berkas (foto bukti: JPG/PNG maks 5 MB).

## 7. Status kerja sama (dihitung, tidak disimpan)

| Status | Aturan (urut prioritas) |
|---|---|
| `dibatalkan` / `dihentikan` | `status_manual` terisi |
| `belum_berlaku` | hari ini < `tanggal_mulai` |
| `kedaluwarsa` | hari ini > `tanggal_berakhir` |
| `akan_berakhir` | sisa ≤ 90 hari — **⚠ ASUMSI** ambang 90 hari |
| `aktif` | selain di atas |

Implementasikan sebagai accessor + query scope (`scopeAktif`, `scopeAkanBerakhir`, …)
agar bisa dipakai di filter tabel Filament dan widget.

## 8. Cakupan & fase

### Fase 1 — MVP (target pertama vibecoding)
1. Kerangka proyek: Laravel 13 + Filament 5 panel `/admin`, Docker, login, role Spatie + seeder 4 role & 1 super admin.
2. Master: Prodi (+ NDTPS per TS), Mitra, Bentuk Kerja Sama (seeder dari `docs/rujukan.md`).
3. Kerja Sama: Filament Resource lengkap — form bertahap (identitas dokumen → para pihak & prodi → masa berlaku → unggah berkas), tabel dengan filter (status, tingkat, jenis dokumen, jenis mitra, prodi, tahun), badge status berwarna, tampilan induk/anak.
4. Realisasi Kegiatan: Relation manager di halaman kerja sama + Resource tersendiri; unggah bukti; alur verifikasi (`admin_prodi` mengajukan → `admin_fakultas` memverifikasi).
5. Dashboard: kartu jumlah aktif / akan berakhir / kedaluwarsa; grafik per tingkat dan per jenis mitra; tabel "akan berakhir 90 hari".
6. Impor awal dari CSV di `contoh-data/` (Artisan command `kerjasama:impor {folder}`), idempoten.
7. Ekspor Excel "Tabel Kerja Sama Tridharma" per prodi & rentang TS (format §9).

### Fase 2
- Pengingat jatuh tempo terjadwal (180/90/30 hari) via database notification + e-mail ke admin fakultas & kaprodi terkait.
- Kalkulator skor LAMDIK elemen Kerja Sama Tridharma per prodi (rumus di `docs/rujukan.md`) + halaman simulasi.
- Evaluasi keefektifan kerja sama (tabel `evaluasi_kerja_sama`).
- Laporan PDF rekap untuk pimpinan.

### Fase 3 (bergantung sistem lain)
- Login tunggal lewat FKIP Edu (lihat `../fkipedu/README.md`).
- Tautan ke Sistem Regulasi (`../regulasi/`) dan Akreditasi (`../akreditasi/`).
- Pelaporan IKU 5 (luaran hasil kerja sama) — tunggu kepastian definisi operasional.

### Di luar cakupan
Alur persetujuan/penandatanganan dokumen secara elektronik, penyusunan naskah perjanjian
dari templat, portal publik untuk mitra.

## 9. Laporan untuk akreditasi

Ekspor per prodi, rentang TS-2 s.d. TS, tiga lembar (Pendidikan, Penelitian, PkM), kolom:

| No | Lembaga Mitra | Tingkat (Internasional/Nasional/Lokal) | Judul Kegiatan Kerja Sama | Manfaat bagi PS | Waktu & Durasi | Bukti Kerja Sama (no. dokumen + tautan berkas) | Tahun Berakhir Kerja Sama |

**⚠ ASUMSI cara menghitung** N1/N2/N3 dan NI/NN/NW: satu baris = satu **realisasi
kegiatan berstatus `terverifikasi`** yang terkait prodi tsb., dikelompokkan menurut
`dharma`; tingkat diambil dari dokumen kerja samanya. Konfirmasi ke GKM/UPM sebelum
fase 2 karena memengaruhi skor.

## 10. Kriteria selesai MVP

- [ ] `docker compose up` → aplikasi terbuka di `http://localhost:8019/admin`.
- [ ] `php artisan migrate:fresh --seed` jalan bersih; ada 4 role, 1 super admin, master bentuk kerja sama terisi.
- [ ] `admin_prodi` tidak bisa mengubah kerja sama yang tidak melibatkan prodinya (uji Policy dengan Pest).
- [ ] Status kerja sama benar untuk 5 kasus di §7 (uji unit).
- [ ] Impor CSV contoh → data muncul; impor ulang tidak menggandakan data.
- [ ] Ekspor Excel menghasilkan 3 lembar dengan kolom §9.
- [ ] Berkas PDF tidak bisa diunduh tanpa login (uji feature).
- [ ] `php artisan test` hijau.

## 11. Status pengembangan

- [x] Analisis kebutuhan & kategori kerja sama (Permendikbud 14/2014, LAMDIK IAPSK 3.0)
- [x] Desain database (v1, §6)
- [ ] Konfirmasi asumsi bertanda ⚠ (lokasi repo, port, ambang hari, cara hitung LAMDIK)
- [ ] Kumpulkan data kerja sama yang sudah ada ke templat `contoh-data/`
- [ ] Fase 1 — MVP
- [ ] Fase 2
- [ ] Deploy & pengujian pengguna

## 12. Menyiapkan repo (langkah pertama vibecoding)

```bash
# di WSL
mkdir -p ~/code && cd ~/code
composer create-project laravel/laravel kerjasama
cd kerjasama
cp /mnt/c/Users/Lenovo/Documents/projects/supportfkip/kerjasama/CLAUDE.md .
git init && git add -A && git commit -m "init: Laravel 13 + spesifikasi"
# lalu buka agen AI di folder ini dan minta: "kerjakan Fase 1 langkah 1 sesuai CLAUDE.md"
```
