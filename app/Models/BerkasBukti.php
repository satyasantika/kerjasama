<?php

namespace App\Models;

use App\Enums\JenisBukti;
use Database\Factories\BerkasBuktiFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BerkasBukti extends Model
{
    /** @use HasFactory<BerkasBuktiFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'berkas_bukti';

    protected $fillable = ['realisasi_kegiatan_id', 'nama_berkas', 'path', 'tautan', 'jenis', 'ukuran_kb'];

    protected static function booted(): void
    {
        static::creating(function (self $berkas) {
            if (! $berkas->ukuran_kb && filled($berkas->path) && Storage::disk('local')->exists($berkas->path)) {
                $berkas->ukuran_kb = (int) ceil(Storage::disk('local')->size($berkas->path) / 1024);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'jenis' => JenisBukti::class,
            'ukuran_kb' => 'integer',
        ];
    }

    public function realisasiKegiatan(): BelongsTo
    {
        return $this->belongsTo(RealisasiKegiatan::class);
    }
}
