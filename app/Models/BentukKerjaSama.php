<?php

namespace App\Models;

use App\Enums\BidangBentuk;
use App\Enums\Dharma;
use App\Enums\RuangKerjaSama;
use Illuminate\Database\Eloquent\Model;

class BentukKerjaSama extends Model
{
    protected $table = 'bentuk_kerja_sama';

    protected $fillable = ['kode', 'nama', 'bidang', 'ruang', 'dharma_default', 'pasal_rujukan', 'aktif'];

    protected function casts(): array
    {
        return [
            'bidang' => BidangBentuk::class,
            'ruang' => RuangKerjaSama::class,
            'dharma_default' => Dharma::class,
            'aktif' => 'boolean',
        ];
    }
}
