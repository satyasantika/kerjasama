<?php

namespace App\Models;

use App\Enums\JenisMitra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mitra extends Model
{
    use SoftDeletes;

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
