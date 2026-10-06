<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdiNdtps extends Model
{
    protected $table = 'prodi_ndtps';

    protected $fillable = ['prodi_id', 'tahun_ts', 'ndtps'];

    protected function casts(): array
    {
        return [
            'tahun_ts' => 'integer',
            'ndtps' => 'integer',
        ];
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }
}
