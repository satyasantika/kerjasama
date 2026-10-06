<?php

namespace App\Filament\Concerns;

use App\Enums\Peran;
use App\Models\RealisasiKegiatan;

/** Menyimpan pivot realisasi_kegiatan_prodi dari isian virtual form. */
trait MenyimpanProdiRealisasi
{
    protected function isiProdiVirtual(array $data): array
    {
        $data['prodi_ids'] = $this->getRecord()->prodi()->pluck('prodi.id')->all();

        return $data;
    }

    protected function simpanProdi(RealisasiKegiatan $record, array $state): void
    {
        $ids = collect($state['prodi_ids'] ?? [])->map(fn ($id) => (int) $id);
        $user = auth()->user();

        if ($user?->hasRole(Peran::AdminProdi->value) && $user->prodi_id) {
            $ids->push((int) $user->prodi_id);
        }

        $record->prodi()->sync($ids->unique()->values()->all());
    }
}
