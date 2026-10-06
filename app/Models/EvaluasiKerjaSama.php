<?php

namespace App\Models;

use App\Enums\RekomendasiEvaluasi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiKerjaSama extends Model
{
    /** @use HasFactory<\Database\Factories\EvaluasiKerjaSamaFactory> */
    use HasFactory;

    protected $table = 'evaluasi_kerja_sama';

    /** Deskriptor skor (b) LAMDIK IAPSK 3.0 — docs/rujukan.md §B.2. */
    public const DESKRIPTOR = [
        4 => '4 — Kontribusi nyata, berkelanjutan, terukur bagi mutu tridharma, serta reputasi PS di tingkat lokal, nasional, internasional',
        3 => '3 — Kontribusi nyata, berkelanjutan, dan terukur bagi mutu tridharma',
        2 => '2 — Kontribusi nyata bagi mutu tridharma',
        1 => '1 — PS tidak menganalisis keefektifan kerja sama',
    ];

    protected $fillable = ['kerja_sama_id', 'tahun_ts', 'skor_keefektifan', 'analisis', 'rekomendasi', 'dinilai_oleh'];

    protected function casts(): array
    {
        return [
            'tahun_ts' => 'integer',
            'skor_keefektifan' => 'integer',
            'rekomendasi' => RekomendasiEvaluasi::class,
        ];
    }

    public function kerjaSama(): BelongsTo
    {
        return $this->belongsTo(KerjaSama::class);
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}
