<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama');
            $table->enum('jenjang', ['S1', 'S2', 'S3', 'PPG', 'Profesi']);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('prodi_ndtps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('prodi_id')->constrained('prodi')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun_ts');
            $table->unsignedSmallInteger('ndtps');
            $table->timestamps();
            $table->unique(['prodi_id', 'tahun_ts']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('prodi_id')->nullable()->after('email')->constrained('prodi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prodi_id');
        });
        Schema::dropIfExists('prodi_ndtps');
        Schema::dropIfExists('prodi');
    }
};
