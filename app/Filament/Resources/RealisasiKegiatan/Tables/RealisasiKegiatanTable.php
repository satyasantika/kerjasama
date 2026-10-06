<?php

namespace App\Filament\Resources\RealisasiKegiatan\Tables;

use App\Enums\Dharma;
use App\Enums\StatusVerifikasi;
use App\Filament\Resources\RealisasiKegiatan\Actions\AksiVerifikasi;
use App\Models\Prodi;
use App\Models\RealisasiKegiatan;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RealisasiKegiatanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('judul_kegiatan')->label('Kegiatan')->searchable()->wrap()->limit(60),
                TextColumn::make('kerjaSama.judul')->label('Kerja sama')->limit(40)->searchable(),
                TextColumn::make('dharma')->label('Dharma')->badge()->sortable(),
                TextColumn::make('prodi.nama')->label('Prodi')->badge()->limitList(2),
                TextColumn::make('tanggal_mulai')->label('Mulai')->date('d/m/Y')->sortable(),
                TextColumn::make('status_verifikasi')->label('Status')->badge()->sortable(),
            ])
            ->defaultSort('tanggal_mulai', 'desc')
            ->filters([
                SelectFilter::make('status_verifikasi')->label('Status verifikasi')->options(StatusVerifikasi::class),
                SelectFilter::make('dharma')->label('Dharma')->options(Dharma::class),
                SelectFilter::make('prodi')
                    ->label('Prodi')
                    ->options(fn () => Prodi::orderBy('nama')->pluck('nama', 'id'))
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('prodi', fn (Builder $q) => $q->where('prodi.id', $data['value'])) : $query),
                SelectFilter::make('tahun')
                    ->label('Tahun mulai')
                    ->options(fn () => RealisasiKegiatan::query()->selectRaw('YEAR(tanggal_mulai) as t')->distinct()->orderByDesc('t')->pluck('t', 't')->all())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereYear('tanggal_mulai', $data['value']) : $query),
                TrashedFilter::make()->label('Data terhapus'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                AksiVerifikasi::ajukan(),
                AksiVerifikasi::verifikasi(),
                AksiVerifikasi::tolak(),
                RestoreAction::make(),
            ]);
    }
}
