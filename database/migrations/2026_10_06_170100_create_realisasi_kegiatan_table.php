<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kerja_sama_id')->constrained('kerja_sama')->restrictOnDelete();
            $table->string('judul_kegiatan');
            $table->text('deskripsi')->nullable();
            $table->enum('dharma', ['pendidikan', 'penelitian', 'pkm']);
            $table->foreignId('bentuk_kerja_sama_id')->nullable()->constrained('bentuk_kerja_sama')->nullOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->text('manfaat_bagi_prodi');
            $table->text('luaran')->nullable();
            $table->unsignedSmallInteger('jumlah_mahasiswa')->default(0);
            $table->unsignedSmallInteger('jumlah_dosen')->default(0);
            $table->enum('status_verifikasi', ['draf', 'diajukan', 'terverifikasi', 'ditolak'])->default('draf');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('diverifikasi_pada')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status_verifikasi');
        });

        Schema::create('realisasi_kegiatan_prodi', function (Blueprint $table) {
            $table->foreignId('realisasi_kegiatan_id')->constrained('realisasi_kegiatan')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('prodi')->restrictOnDelete();
            $table->primary(['realisasi_kegiatan_id', 'prodi_id']);
        });

        Schema::create('berkas_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_kegiatan_id')->constrained('realisasi_kegiatan')->restrictOnDelete();
            $table->string('nama_berkas');
            $table->string('path');
            $table->enum('jenis', ['laporan', 'daftar_hadir', 'foto', 'surat_tugas', 'lainnya']);
            $table->unsignedInteger('ukuran_kb')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_bukti');
        Schema::dropIfExists('realisasi_kegiatan_prodi');
        Schema::dropIfExists('realisasi_kegiatan');
    }
};
