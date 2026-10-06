<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bentuk_kerja_sama', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->enum('bidang', ['akademik', 'non_akademik']);
            $table->enum('ruang', ['antar_pt', 'dudi_pihak_lain']);
            $table->enum('dharma_default', ['pendidikan', 'penelitian', 'pkm'])->nullable();
            $table->string('pasal_rujukan');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bentuk_kerja_sama');
    }
};
