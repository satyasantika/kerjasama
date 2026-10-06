<?php

namespace App\Services;

use App\Enums\Peran;
use App\Models\KerjaSama;
use App\Models\PengingatTerkirim;
use App\Models\User;
use App\Notifications\PengingatJatuhTempo as NotifikasiPengingat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Pengingat jatuh tempo bertahap (bawaan 180/90/30 hari). Setiap kerja sama hanya
 * mendapat satu pengingat per ambang (tabel pengingat_terkirim). Bila baru masuk sistem
 * dengan sisa lebih sedikit, hanya ambang terkecil yang terlewati yang dikirim; ambang
 * yang lebih besar ditandai terkirim agar tidak menyusul.
 */
class PengingatJatuhTempo
{
    /** @return int jumlah kerja sama yang diberi pengingat */
    public function kirim(): int
    {
        $ambang = collect(config('kerjasama.ambang_pengingat'))->map(fn ($a) => (int) $a)->sort()->values();

        if ($ambang->isEmpty()) {
            return 0;
        }

        $terkirim = 0;

        KerjaSama::query()
            ->whereNull('status_manual')
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_berakhir', '>=', today())
            ->whereDate('tanggal_berakhir', '<=', today()->addDays($ambang->max()))
            ->with(['mitra', 'prodi'])
            ->orderBy('id')
            ->each(function (KerjaSama $ks) use ($ambang, &$terkirim) {
                $sisa = $ks->sisa_hari;
                $terlewati = $ambang->filter(fn (int $a) => $sisa <= $a);
                $sudah = PengingatTerkirim::where('kerja_sama_id', $ks->id)->pluck('ambang_hari');
                $belum = $terlewati->diff($sudah);

                if ($belum->isEmpty()) {
                    return;
                }

                $ambangKirim = $terlewati->min();

                if ($sudah->contains($ambangKirim)) {
                    // ambang terkecil sudah terkirim; tandai sisanya saja
                    $this->tandai($ks, $belum);

                    return;
                }

                $penerima = $this->penerima($ks);

                if ($penerima->isNotEmpty()) {
                    Notification::send($penerima, new NotifikasiPengingat($ks, $ambangKirim));
                    $terkirim++;
                }

                $this->tandai($ks, $belum);
            });

        return $terkirim;
    }

    /** admin_fakultas (semua) dan pimpinan/kaprodi yang prodinya terlibat. */
    public function penerima(KerjaSama $ks): Collection
    {
        $prodiIds = $ks->prodi->pluck('id');

        return User::role(Peran::AdminFakultas->value)->get()
            ->merge(User::role(Peran::Pimpinan->value)->whereIn('prodi_id', $prodiIds)->get())
            ->unique('id')
            ->values();
    }

    private function tandai(KerjaSama $ks, Collection $ambang): void
    {
        foreach ($ambang as $a) {
            PengingatTerkirim::firstOrCreate(
                ['kerja_sama_id' => $ks->id, 'ambang_hari' => $a],
                ['dikirim_pada' => now()],
            );
        }
    }
}
