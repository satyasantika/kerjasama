<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengingatTerkirim extends Model
{
    protected $table = 'pengingat_terkirim';

    public $timestamps = false;

    protected $fillable = ['kerja_sama_id', 'ambang_hari', 'dikirim_pada'];

    protected function casts(): array
    {
        return ['ambang_hari' => 'integer', 'dikirim_pada' => 'datetime'];
    }

    public function kerjaSama(): BelongsTo
    {
        return $this->belongsTo(KerjaSama::class);
    }
}
