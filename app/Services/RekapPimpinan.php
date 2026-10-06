<?php

namespace App\Services;

use App\Enums\JenisMitra;
use App\Enums\StatusKerjaSama;
use App\Enums\TingkatKerjaSama;
use App\Models\KerjaSama;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Data rekap kerja sama untuk laporan PDF pimpinan; angka mengikuti widget dashboard. */
class RekapPimpinan
{
    /** @return array<string, mixed> */
    public function data(): array
    {
        return [
            'dicetak' => now(),
            'ambang' => StatusKerjaSama::ambangAkanBerakhir(),
            'status' => collect([StatusKerjaSama::Aktif, StatusKerjaSama::AkanBerakhir, StatusKerjaSama::Kedaluwarsa, StatusKerjaSama::BelumBerlaku])
                ->mapWithKeys(fn (StatusKerjaSama $s) => [$s->label() => KerjaSama::status($s)->count()])
                ->all(),
            'perTingkat' => $this->perTingkat(),
            'perJenisMitra' => $this->perJenisMitra(),
            'akanBerakhir' => $this->daftar(KerjaSama::akanBerakhir()),
            'kedaluwarsa' => $this->daftar(KerjaSama::kedaluwarsa()->orderByDesc('tanggal_berakhir')),
        ];
    }

    /** @return array<string, int> */
    private function perTingkat(): array
    {
        $jumlah = KerjaSama::berlaku()->selectRaw('tingkat, count(*) as total')->groupBy('tingkat')->pluck('total', 'tingkat');

        return collect(TingkatKerjaSama::cases())->mapWithKeys(fn ($t) => [$t->label() => (int) ($jumlah[$t->value] ?? 0)])->all();
    }

    /** @return array<string, int> */
    private function perJenisMitra(): array
    {
        $jumlah = KerjaSama::berlaku()
            ->join('kerja_sama_mitra', 'kerja_sama_mitra.kerja_sama_id', '=', 'kerja_sama.id')
            ->join('mitra', 'mitra.id', '=', 'kerja_sama_mitra.mitra_id')
            ->select('mitra.jenis', DB::raw('count(distinct kerja_sama.id) as total'))
            ->groupBy('mitra.jenis')
            ->pluck('total', 'jenis');

        return collect(JenisMitra::cases())->mapWithKeys(fn ($j) => [$j->label() => (int) ($jumlah[$j->value] ?? 0)])->all();
    }

    /** @return Collection<int, KerjaSama> */
    private function daftar($query): Collection
    {
        return $query->with(['mitra', 'prodi'])->orderBy('tanggal_berakhir')->get();
    }
}
