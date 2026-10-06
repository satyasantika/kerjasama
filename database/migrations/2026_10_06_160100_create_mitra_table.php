<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitra', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->enum('jenis', ['perguruan_tinggi', 'sekolah', 'pemerintah', 'dudi', 'organisasi_profesi', 'lembaga_lain']);
            $table->string('negara')->default('Indonesia');
            $table->string('provinsi')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->text('alamat')->nullable();
            $table->string('website')->nullable();
            $table->string('kontak_nama')->nullable();
            $table->string('kontak_jabatan')->nullable();
            $table->string('kontak_email')->nullable();
            $table->string('kontak_telepon')->nullable();
            $table->string('status_legal')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['nama', 'negara']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitra');
    }
};
