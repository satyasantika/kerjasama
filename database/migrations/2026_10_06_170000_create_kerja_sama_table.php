<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kerja_sama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('induk_id')->nullable()->constrained('kerja_sama')->nullOnDelete();
            $table->string('kode_impor')->nullable()->unique();
            $table->enum('jenis_dokumen', ['MoU', 'MoA', 'PKS', 'IA']);
            $table->string('nomor_dokumen_unsil')->nullable();
            $table->string('nomor_dokumen_mitra')->nullable();
            $table->string('judul');
            $table->text('ruang_lingkup');
            $table->enum('bidang', ['akademik', 'non_akademik', 'keduanya']);
            $table->enum('tingkat', ['lokal', 'nasional', 'internasional']);
            $table->enum('pihak_penandatangan_unsil', ['rektor', 'dekan', 'wakil_dekan', 'kaprodi', 'lainnya']);
            $table->string('nama_penandatangan_unsil')->nullable();
            $table->string('nama_penandatangan_mitra')->nullable();
            $table->string('jabatan_penandatangan_mitra')->nullable();
            $table->date('tanggal_tanda_tangan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');
            $table->enum('status_manual', ['dibatalkan', 'dihentikan'])->nullable();
            $table->boolean('memuat_hki_aset')->default(false);
            $table->boolean('perlu_persetujuan_dirjen')->default(false);
            $table->boolean('sudah_dilaporkan_pddikti')->default(false);
            $table->string('berkas_dokumen')->nullable();
            $table->string('berkas_dokumen_asing')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tanggal_berakhir');
        });

        Schema::create('kerja_sama_mitra', function (Blueprint $table) {
            $table->foreignId('kerja_sama_id')->constrained('kerja_sama')->cascadeOnDelete();
            $table->foreignId('mitra_id')->constrained('mitra')->restrictOnDelete();
            $table->primary(['kerja_sama_id', 'mitra_id']);
        });

        Schema::create('kerja_sama_prodi', function (Blueprint $table) {
            $table->foreignId('kerja_sama_id')->constrained('kerja_sama')->cascadeOnDelete();
            $table->foreignId('prodi_id')->constrained('prodi')->restrictOnDelete();
            $table->boolean('penginisiasi')->default(false);
            $table->primary(['kerja_sama_id', 'prodi_id']);
        });

        Schema::create('kerja_sama_bentuk', function (Blueprint $table) {
            $table->foreignId('kerja_sama_id')->constrained('kerja_sama')->cascadeOnDelete();
            $table->foreignId('bentuk_kerja_sama_id')->constrained('bentuk_kerja_sama')->restrictOnDelete();
            $table->primary(['kerja_sama_id', 'bentuk_kerja_sama_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerja_sama_bentuk');
        Schema::dropIfExists('kerja_sama_prodi');
        Schema::dropIfExists('kerja_sama_mitra');
        Schema::dropIfExists('kerja_sama');
    }
};
