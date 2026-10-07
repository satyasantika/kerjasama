<?php

namespace App\Filament\Resources\KerjaSama\Tables;

use App\Enums\JenisDokumen;
use App\Enums\JenisMitra;
use App\Enums\StatusKerjaSama;
use App\Enums\TingkatKerjaSama;
use App\Models\KerjaSama;
use App\Models\Prodi;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KerjaSamaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('judul')->label('Judul')->searchable()->wrap()->limit(60),
                TextColumn::make('jenis_dokumen')->label('Jenis')->badge()->sortable(),
                TextColumn::make('mitra.nama')->label('Mitra')->badge()->limitList(2)->expandableLimitedList(),
                TextColumn::make('tingkat')->label('Tingkat')->badge()->sortable(),
                TextColumn::make('tanggal_mulai')->label('Mulai')->date('d/m/Y')->sortable(),
                TextColumn::make('tanggal_berakhir')->label('Berakhir')->date('d/m/Y')->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('induk.judul')->label('Induk')->limit(40)->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_berakhir')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(StatusKerjaSama::class)
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->status(StatusKerjaSama::from($data['value'])) : $query),
                SelectFilter::make('tingkat')->label('Tingkat')->options(TingkatKerjaSama::class),
                SelectFilter::make('jenis_dokumen')->label('Jenis dokumen')->options(JenisDokumen::class),
                SelectFilter::make('jenis_mitra')
                    ->label('Jenis mitra')
                    ->options(JenisMitra::class)
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas('mitra', fn (Builder $q) => $q->where('jenis', $data['value'])) : $query),
                SelectFilter::make('prodi')
                    ->label('Prodi')
                    ->options(fn () => Prodi::orderBy('nama')->pluck('nama', 'id'))
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->melibatkanProdi($data['value']) : $query),
                SelectFilter::make('tahun')
                    ->label('Tahun tanda tangan')
                    ->options(fn () => KerjaSama::query()->selectRaw('YEAR(tanggal_tanda_tangan) as t')->distinct()->orderByDesc('t')->pluck('t', 't')->all())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereYear('tanggal_tanda_tangan', $data['value']) : $query),
                TrashedFilter::make()->label('Data terhapus'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                RestoreAction::make(),
            ]);
    }
}
