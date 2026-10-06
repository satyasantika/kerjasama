<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengingat_terkirim', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kerja_sama_id')->constrained('kerja_sama')->cascadeOnDelete();
            $table->unsignedSmallInteger('ambang_hari');
            $table->dateTime('dikirim_pada');
            $table->unique(['kerja_sama_id', 'ambang_hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengingat_terkirim');
    }
};
