<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kerja_sama', function (Blueprint $table) {
            $table->string('tautan_dokumen', 500)->nullable()->after('berkas_dokumen_asing');
            $table->string('tautan_dokumen_asing', 500)->nullable()->after('tautan_dokumen');
        });

        Schema::table('berkas_bukti', function (Blueprint $table) {
            $table->string('path')->nullable()->change();
            $table->string('tautan', 500)->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('berkas_bukti', function (Blueprint $table) {
            $table->dropColumn('tautan');
        });

        Schema::table('kerja_sama', function (Blueprint $table) {
            $table->dropColumn(['tautan_dokumen', 'tautan_dokumen_asing']);
        });
    }
};
