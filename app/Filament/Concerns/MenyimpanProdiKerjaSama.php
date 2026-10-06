<?php

namespace App\Filament\Concerns;

use App\Enums\Peran;
use App\Models\KerjaSama;

/** Menyimpan pivot kerja_sama_prodi (dengan kolom penginisiasi) dari isian virtual form. */
trait MenyimpanProdiKerjaSama
{
    protected function isiProdiVirtual(array $data): array
    {
        $prodi = $this->getRecord()->prodi()->get();
        $data['prodi_ids'] = $prodi->pluck('id')->all();
        $data['prodi_penginisiasi_ids'] = $prodi->where('pivot.penginisiasi', true)->pluck('id')->all();

        return $data;
    }

    protected function simpanProdi(KerjaSama $record, array $state): void
    {
        $ids = collect($state['prodi_ids'] ?? [])->map(fn ($id) => (string) $id);
        $user = auth()->user();

        if ($user?->hasRole(Peran::AdminProdi->value) && $user->prodi_id) {
            $ids->push((string) $user->prodi_id);
        }

        $ids = $ids->unique()->values();
        $inisiator = collect($state['prodi_penginisiasi_ids'] ?? [])->map(fn ($id) => (string) $id);

        $record->prodi()->sync($ids->mapWithKeys(fn (string $id) => [
            $id => ['penginisiasi' => $inisiator->contains($id)],
        ])->all());
    }
}
