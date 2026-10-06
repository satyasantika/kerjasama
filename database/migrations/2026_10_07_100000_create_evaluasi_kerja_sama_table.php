<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_kerja_sama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kerja_sama_id')->constrained('kerja_sama')->restrictOnDelete();
            $table->unsignedSmallInteger('tahun_ts');
            $table->unsignedTinyInteger('skor_keefektifan');
            $table->text('analisis');
            $table->enum('rekomendasi', ['lanjutkan', 'perpanjang', 'revisi', 'hentikan']);
            $table->foreignId('dinilai_oleh')->constrained('users');
            $table->timestamps();

            $table->index(['kerja_sama_id', 'tahun_ts']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_kerja_sama');
    }
};
