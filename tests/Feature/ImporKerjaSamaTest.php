<?php

use App\Enums\Peran;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\Prodi;
use App\Models\RealisasiKegiatan;
use App\Models\User;
use Database\Seeders\BentukKerjaSamaSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed([RolSeeder::class, BentukKerjaSamaSeeder::class]);
    User::factory()->create(['email' => 'admin@kerjasama.test'])->assignRole(Peran::SuperAdmin->value);

    $this->folder = sys_get_temp_dir().'/impor-'.uniqid();
    File::ensureDirectoryExists($this->folder.'/berkas');

    File::put($this->folder.'/prodi.csv', <<<'CSV'
kode,nama,jenjang,aktif
PMAT,Pendidikan Matematika,S1,1
PBIO,Pendidikan Biologi,S1,1
CONTOH-PX,Pendidikan Contoh,S1,1
CSV);
    File::put($this->folder.'/mitra.csv', <<<'CSV'
nama,jenis,negara,provinsi,kabupaten_kota,alamat,website,kontak_nama,kontak_jabatan,kontak_email,kontak_telepon,status_legal,catatan
SMA Negeri 1 Tasikmalaya,sekolah,Indonesia,Jawa Barat,Kota Tasikmalaya,"Jl. Merdeka No. 1, Tasikmalaya",,,,,,,Mitra PLP
Universiti Contoh,perguruan_tinggi,Malaysia,,,,https://contoh.edu.my,,,,,Terakreditasi,
CONTOH SMA,sekolah,Indonesia,,,,,,,,,,
CSV);
    File::put($this->folder.'/kerja_sama.csv', <<<'CSV'
kode_impor,kode_impor_induk,jenis_dokumen,nomor_dokumen_unsil,nomor_dokumen_mitra,judul,ruang_lingkup,bidang,tingkat,pihak_penandatangan_unsil,nama_penandatangan_unsil,nama_penandatangan_mitra,jabatan_penandatangan_mitra,tanggal_tanda_tangan,tanggal_mulai,tanggal_berakhir,mitra,prodi,prodi_penginisiasi,bentuk,memuat_hki_aset,perlu_persetujuan_dirjen,sudah_dilaporkan_pddikti,berkas_dokumen
KS-001,,MoU,001/KS/2024,,MoU Akademik,Pertukaran dosen,akademik,lokal,rektor,,,,2024-06-01,2024-06-01,2029-05-31,Universiti Contoh,PMAT|PBIO,PMAT,AK-PT-02|AK-PT-10,0,0,1,
KS-002,KS-001,MoA,002/KS/2025,,MoA PLP,Penempatan PLP,akademik,lokal,dekan,,,,2025-01-15,2025-01-15,2028-01-14,SMA Negeri 1 Tasikmalaya,PMAT,PMAT,FKIP-01|AK-DU-01,0,0,0,perjanjian.pdf
CONTOH-KS-001,,MoA,000,,Contoh,Contoh,akademik,lokal,dekan,,,,2025-01-15,2025-01-15,2028-01-14,CONTOH SMA,CONTOH-PX,CONTOH-PX,FKIP-01,0,0,0,
CSV);
    File::put($this->folder.'/berkas/perjanjian.pdf', '%PDF-1.4 contoh');
    File::put($this->folder.'/realisasi_kegiatan.csv', <<<'CSV'
kode_impor_kerja_sama,judul_kegiatan,deskripsi,dharma,bentuk,tanggal_mulai,tanggal_selesai,manfaat_bagi_prodi,luaran,jumlah_mahasiswa,jumlah_dosen,prodi,status_verifikasi
KS-002,PLP II 2025,Penempatan mahasiswa,,FKIP-01,2025-08-25,2025-11-28,Lahan praktik,Laporan PLP,20,3,PMAT,terverifikasi
KS-001,Riset bersama,Kolaborasi,penelitian,AK-PT-02,2025-03-01,2025-12-31,Publikasi bersama,Artikel,2,4,PMAT|PBIO,diajukan
CSV);
});

afterEach(fn () => File::deleteDirectory($this->folder));

it('mengimpor semua berkas CSV dan melewati baris CONTOH', function () {
    Storage::fake('local');

    $this->artisan('kerjasama:impor', ['folder' => $this->folder])->assertSuccessful();

    expect(Prodi::pluck('kode')->sort()->values()->all())->toBe(['PBIO', 'PMAT'])
        ->and(Mitra::count())->toBe(2)
        ->and(KerjaSama::count())->toBe(2)
        ->and(RealisasiKegiatan::count())->toBe(2);

    $ks1 = KerjaSama::firstWhere('kode_impor', 'KS-001');
    $ks2 = KerjaSama::firstWhere('kode_impor', 'KS-002');

    expect($ks2->induk_id)->toBe($ks1->id)
        ->and($ks1->prodi->pluck('kode')->sort()->values()->all())->toBe(['PBIO', 'PMAT'])
        ->and($ks1->prodi->firstWhere('kode', 'PMAT')->pivot->penginisiasi)->toBe(1)
        ->and($ks1->prodi->firstWhere('kode', 'PBIO')->pivot->penginisiasi)->toBe(0)
        ->and($ks1->bentuk->pluck('kode')->sort()->values()->all())->toBe(['AK-PT-02', 'AK-PT-10'])
        ->and($ks1->sudah_dilaporkan_pddikti)->toBeTrue()
        ->and($ks1->dibuat_oleh)->toBe(User::firstWhere('email', 'admin@kerjasama.test')->id);

    // mitra asing → internasional + persetujuan Dirjen, walau CSV mengisi lokal/0
    expect($ks1->tingkat->value)->toBe('internasional')
        ->and($ks1->perlu_persetujuan_dirjen)->toBeTrue()
        ->and($ks2->tingkat->value)->toBe('lokal');

    // berkas PDF disalin ke disk privat
    Storage::disk('local')->assertExists($ks2->berkas_dokumen);

    $plp = RealisasiKegiatan::firstWhere('judul_kegiatan', 'PLP II 2025');

    expect($plp->dharma->value)->toBe('pendidikan')               // dari bentuk FKIP-01
        ->and($plp->status_verifikasi->value)->toBe('terverifikasi')
        ->and($plp->prodi->pluck('kode')->all())->toBe(['PMAT'])
        ->and(Mitra::firstWhere('nama', 'SMA Negeri 1 Tasikmalaya')->alamat)->toBe('Jl. Merdeka No. 1, Tasikmalaya');
});

it('impor ulang tidak menggandakan data', function () {
    Storage::fake('local');

    $this->artisan('kerjasama:impor', ['folder' => $this->folder])->assertSuccessful();
    $hitung = fn () => [Prodi::count(), Mitra::count(), KerjaSama::count(), RealisasiKegiatan::count(),
        \DB::table('kerja_sama_prodi')->count(), \DB::table('kerja_sama_mitra')->count(), \DB::table('kerja_sama_bentuk')->count(),
        \DB::table('realisasi_kegiatan_prodi')->count()];
    $pertama = $hitung();

    $this->artisan('kerjasama:impor', ['folder' => $this->folder])->assertSuccessful();

    expect($hitung())->toBe($pertama)->and($pertama)->toBe([2, 2, 2, 2, 3, 2, 4, 3]);
});

it('impor ulang memperbarui data yang berubah', function () {
    Storage::fake('local');
    $this->artisan('kerjasama:impor', ['folder' => $this->folder])->assertSuccessful();

    File::put($this->folder.'/prodi.csv', "kode,nama,jenjang,aktif\nPMAT,Pendidikan Matematika (baru),S1,0\n");
    $this->artisan('kerjasama:impor', ['folder' => $this->folder])->assertSuccessful();

    $p = Prodi::firstWhere('kode', 'PMAT');

    expect($p->nama)->toBe('Pendidikan Matematika (baru)')->and($p->aktif)->toBeFalse();
});

it('baris contoh-data bawaan dilewati seluruhnya', function () {
    $this->artisan('kerjasama:impor', ['folder' => base_path('contoh-data')])->assertSuccessful();

    expect(Prodi::count() + Mitra::count() + KerjaSama::count() + RealisasiKegiatan::count())->toBe(0);
})->skip(fn () => ! is_dir(base_path('contoh-data')), 'folder contoh-data tidak ada');

it('melaporkan baris bermasalah, melewatinya, dan gagal dengan kode non-nol', function () {
    File::put($this->folder.'/kerja_sama.csv', <<<'CSV'
kode_impor,kode_impor_induk,jenis_dokumen,nomor_dokumen_unsil,nomor_dokumen_mitra,judul,ruang_lingkup,bidang,tingkat,pihak_penandatangan_unsil,nama_penandatangan_unsil,nama_penandatangan_mitra,jabatan_penandatangan_mitra,tanggal_tanda_tangan,tanggal_mulai,tanggal_berakhir,mitra,prodi,prodi_penginisiasi,bentuk,memuat_hki_aset,perlu_persetujuan_dirjen,sudah_dilaporkan_pddikti,berkas_dokumen
KS-OK,,MoA,,,Baik,Lingkup,akademik,lokal,dekan,,,,2025-01-15,2025-01-15,2028-01-14,SMA Negeri 1 Tasikmalaya,PMAT,,,0,0,0,
KS-ENUM,,XXX,,,Salah jenis,Lingkup,akademik,lokal,dekan,,,,2025-01-15,2025-01-15,2028-01-14,SMA Negeri 1 Tasikmalaya,PMAT,,,0,0,0,
KS-MITRA,,MoA,,,Mitra hilang,Lingkup,akademik,lokal,dekan,,,,2025-01-15,2025-01-15,2028-01-14,Tidak Ada,PMAT,,,0,0,0,
KS-TGL,,MoA,,,Tanggal salah,Lingkup,akademik,lokal,dekan,,,,2025-01-15,2025-01-15,2025-01-01,SMA Negeri 1 Tasikmalaya,PMAT,,,0,0,0,
CSV);
    File::delete($this->folder.'/realisasi_kegiatan.csv');

    $this->artisan('kerjasama:impor', ['folder' => $this->folder])
        ->expectsOutputToContain('kerja_sama.csv baris 3')
        ->expectsOutputToContain('Tidak Ada')
        ->assertFailed();

    expect(KerjaSama::pluck('kode_impor')->all())->toBe(['KS-OK']);
});

it('mitra luar negeri tanpa status legal ditolak', function () {
    File::put($this->folder.'/mitra.csv', "nama,jenis,negara,status_legal\nUniv Asing,perguruan_tinggi,Jepang,\n");

    $hasil = (new \App\Services\ImporKerjaSama($this->folder, User::first()))->jalankan();

    expect(Mitra::where('nama', 'Univ Asing')->exists())->toBeFalse()
        ->and(implode(' ', $hasil['galat']))->toContain('status legal');
});

it('tidak menghidupkan kembali data yang sudah dihapus', function () {
    Storage::fake('local');
    $this->artisan('kerjasama:impor', ['folder' => $this->folder])->assertSuccessful();
    KerjaSama::firstWhere('kode_impor', 'KS-002')->delete();

    $this->artisan('kerjasama:impor', ['folder' => $this->folder])
        ->expectsOutputToContain('sudah dihapus')
        ->assertFailed();   // realisasi KS-002 kini tidak ditemukan → galat

    expect(KerjaSama::count())->toBe(1);
});

it('gagal bila folder tidak ada atau tidak ada pengguna pencatat', function () {
    $this->artisan('kerjasama:impor', ['folder' => '/tidak/ada'])->assertFailed();

    User::query()->delete();
    $this->artisan('kerjasama:impor', ['folder' => $this->folder])->assertFailed();
});
