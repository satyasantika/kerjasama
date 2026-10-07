<?php

namespace App\Models;

use App\Enums\JenisMitra;
use Database\Factories\MitraFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mitra extends Model
{
    /** @use HasFactory<MitraFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'mitra';

    protected $fillable = [
        'nama', 'jenis', 'negara', 'provinsi', 'kabupaten_kota', 'alamat', 'website',
        'kontak_nama', 'kontak_jabatan', 'kontak_email', 'kontak_telepon',
        'status_legal', 'catatan',
    ];

    protected function casts(): array
    {
        return ['jenis' => JenisMitra::class];
    }

    public function getAsingAttribute(): bool
    {
        return strcasecmp((string) $this->negara, 'Indonesia') !== 0;
    }
}
