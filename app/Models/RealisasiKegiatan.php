<?php

namespace App\Models;

use App\Enums\Dharma;
use App\Enums\JenisBukti;
use App\Enums\StatusVerifikasi;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealisasiKegiatan extends Model
{
    /** @use HasFactory<\Database\Factories\RealisasiKegiatanFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'realisasi_kegiatan';

    protected $fillable = [
        'kerja_sama_id', 'judul_kegiatan', 'deskripsi', 'dharma', 'bentuk_kerja_sama_id',
        'tanggal_mulai', 'tanggal_selesai', 'manfaat_bagi_prodi', 'luaran',
        'jumlah_mahasiswa', 'jumlah_dosen', 'status_verifikasi',
        'diverifikasi_oleh', 'diverifikasi_pada', 'catatan_verifikasi', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'dharma' => Dharma::class,
            'status_verifikasi' => StatusVerifikasi::class,
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'diverifikasi_pada' => 'datetime',
            'jumlah_mahasiswa' => 'integer',
            'jumlah_dosen' => 'integer',
        ];
    }

    public function kerjaSama(): BelongsTo
    {
        return $this->belongsTo(KerjaSama::class);
    }

    public function bentuk(): BelongsTo
    {
        return $this->belongsTo(BentukKerjaSama::class, 'bentuk_kerja_sama_id');
    }

    public function prodi(): BelongsToMany
    {
        return $this->belongsToMany(Prodi::class, 'realisasi_kegiatan_prodi');
    }

    public function berkasBukti(): HasMany
    {
        return $this->hasMany(BerkasBukti::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function melibatkanProdi(?string $prodiId): bool
    {
        return $prodiId !== null && $this->prodi()->where('prodi.id', $prodiId)->exists();
    }

    /** Draf atau ditolak = masih boleh disunting pengusul. */
    public function bisaDisunting(): bool
    {
        return in_array($this->status_verifikasi, [StatusVerifikasi::Draf, StatusVerifikasi::Ditolak], true);
    }

    /** docs/rujukan.md §B.5: wajib minimal satu bukti berjenis laporan sebelum terverifikasi. */
    public function punyaLaporan(): bool
    {
        return $this->berkasBukti()->where('jenis', JenisBukti::Laporan->value)->exists();
    }

    public function ajukan(): void
    {
        if (! $this->bisaDisunting()) {
            throw new DomainException('Hanya realisasi berstatus draf atau ditolak yang dapat diajukan.');
        }

        $this->update([
            'status_verifikasi' => StatusVerifikasi::Diajukan,
            'catatan_verifikasi' => null,
            'diverifikasi_oleh' => null,
            'diverifikasi_pada' => null,
        ]);
    }

    public function verifikasi(User $oleh): void
    {
        $this->pastikanDiajukan();

        if (! $this->punyaLaporan()) {
            throw new DomainException('Unggah minimal satu bukti berjenis laporan sebelum diverifikasi.');
        }

        $this->update([
            'status_verifikasi' => StatusVerifikasi::Terverifikasi,
            'diverifikasi_oleh' => $oleh->id,
            'diverifikasi_pada' => now(),
            'catatan_verifikasi' => null,
        ]);
    }

    public function tolak(User $oleh, string $catatan): void
    {
        $this->pastikanDiajukan();

        if (blank($catatan)) {
            throw new DomainException('Catatan penolakan wajib diisi.');
        }

        $this->update([
            'status_verifikasi' => StatusVerifikasi::Ditolak,
            'diverifikasi_oleh' => $oleh->id,
            'diverifikasi_pada' => now(),
            'catatan_verifikasi' => $catatan,
        ]);
    }

    private function pastikanDiajukan(): void
    {
        if ($this->status_verifikasi !== StatusVerifikasi::Diajukan) {
            throw new DomainException('Hanya realisasi berstatus diajukan yang dapat diproses.');
        }
    }
}
