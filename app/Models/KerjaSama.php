<?php

namespace App\Models;

use App\Enums\BidangKerjaSama;
use App\Enums\JenisDokumen;
use App\Enums\PihakPenandatangan;
use App\Enums\StatusKerjaSama;
use App\Enums\StatusManual;
use App\Enums\TingkatKerjaSama;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KerjaSama extends Model
{
    /** @use HasFactory<\Database\Factories\KerjaSamaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'kerja_sama';

    protected $fillable = [
        'induk_id', 'kode_impor', 'jenis_dokumen', 'nomor_dokumen_unsil', 'nomor_dokumen_mitra',
        'judul', 'ruang_lingkup', 'bidang', 'tingkat', 'pihak_penandatangan_unsil',
        'nama_penandatangan_unsil', 'nama_penandatangan_mitra', 'jabatan_penandatangan_mitra',
        'tanggal_tanda_tangan', 'tanggal_mulai', 'tanggal_berakhir', 'status_manual',
        'memuat_hki_aset', 'perlu_persetujuan_dirjen', 'sudah_dilaporkan_pddikti',
        'berkas_dokumen', 'berkas_dokumen_asing', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'jenis_dokumen' => JenisDokumen::class,
            'bidang' => BidangKerjaSama::class,
            'tingkat' => TingkatKerjaSama::class,
            'pihak_penandatangan_unsil' => PihakPenandatangan::class,
            'status_manual' => StatusManual::class,
            'tanggal_tanda_tangan' => 'date',
            'tanggal_mulai' => 'date',
            'tanggal_berakhir' => 'date',
            'memuat_hki_aset' => 'boolean',
            'perlu_persetujuan_dirjen' => 'boolean',
            'sudah_dilaporkan_pddikti' => 'boolean',
        ];
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(self::class, 'induk_id');
    }

    public function mitra(): BelongsToMany
    {
        return $this->belongsToMany(Mitra::class, 'kerja_sama_mitra');
    }

    public function prodi(): BelongsToMany
    {
        return $this->belongsToMany(Prodi::class, 'kerja_sama_prodi')->withPivot('penginisiasi');
    }

    public function bentuk(): BelongsToMany
    {
        return $this->belongsToMany(BentukKerjaSama::class, 'kerja_sama_bentuk');
    }

    public function realisasi(): HasMany
    {
        return $this->hasMany(RealisasiKegiatan::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function getStatusAttribute(): StatusKerjaSama
    {
        return StatusKerjaSama::hitung($this->status_manual, $this->tanggal_mulai, $this->tanggal_berakhir);
    }

    public function getSisaHariAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->tanggal_berakhir->copy()->startOfDay(), false);
    }

    public function melibatkanProdi(?int $prodiId): bool
    {
        return $prodiId !== null && $this->prodi()->where('prodi.id', $prodiId)->exists();
    }

    public function punyaMitraAsing(): bool
    {
        return $this->mitra()->where('negara', '!=', 'Indonesia')->exists();
    }

    /** Aturan Permendikbud 14/2014 Ps. 49 ay. 1 dan README §6: mitra asing → internasional + persetujuan Dirjen. */
    public function terapkanAturanMitraAsing(): void
    {
        if ($this->punyaMitraAsing()) {
            $this->forceFill([
                'tingkat' => TingkatKerjaSama::Internasional,
                'perlu_persetujuan_dirjen' => true,
            ])->save();
        }
    }

    public function scopeStatus(Builder $query, StatusKerjaSama $status): Builder
    {
        return match ($status) {
            StatusKerjaSama::Dibatalkan => $query->where('status_manual', StatusManual::Dibatalkan->value),
            StatusKerjaSama::Dihentikan => $query->where('status_manual', StatusManual::Dihentikan->value),
            StatusKerjaSama::BelumBerlaku => $query->whereNull('status_manual')->whereDate('tanggal_mulai', '>', today()),
            StatusKerjaSama::Kedaluwarsa => $query->whereNull('status_manual')
                ->whereDate('tanggal_mulai', '<=', today())
                ->whereDate('tanggal_berakhir', '<', today()),
            StatusKerjaSama::AkanBerakhir => $query->whereNull('status_manual')
                ->whereDate('tanggal_mulai', '<=', today())
                ->whereDate('tanggal_berakhir', '>=', today())
                ->whereDate('tanggal_berakhir', '<=', today()->addDays(StatusKerjaSama::ambangAkanBerakhir())),
            StatusKerjaSama::Aktif => $query->whereNull('status_manual')
                ->whereDate('tanggal_mulai', '<=', today())
                ->whereDate('tanggal_berakhir', '>', today()->addDays(StatusKerjaSama::ambangAkanBerakhir())),
        };
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->status(StatusKerjaSama::Aktif);
    }

    public function scopeAkanBerakhir(Builder $query): Builder
    {
        return $query->status(StatusKerjaSama::AkanBerakhir);
    }

    public function scopeKedaluwarsa(Builder $query): Builder
    {
        return $query->status(StatusKerjaSama::Kedaluwarsa);
    }

    public function scopeBelumBerlaku(Builder $query): Builder
    {
        return $query->status(StatusKerjaSama::BelumBerlaku);
    }

    public function scopeMelibatkanProdi(Builder $query, int $prodiId): Builder
    {
        return $query->whereHas('prodi', fn (Builder $q) => $q->where('prodi.id', $prodiId));
    }
}
