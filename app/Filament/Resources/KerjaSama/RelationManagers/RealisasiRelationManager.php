<?php

namespace App\Filament\Resources\KerjaSama\RelationManagers;

use App\Filament\Resources\RealisasiKegiatan\RealisasiKegiatanResource;
use App\Models\RealisasiKegiatan;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class RealisasiRelationManager extends RelationManager
{
    protected static string $relationship = 'realisasi';

    protected static ?string $title = 'Realisasi kegiatan';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('judul_kegiatan')
            ->columns([
                TextColumn::make('judul_kegiatan')->label('Kegiatan')->wrap(),
                TextColumn::make('dharma')->label('Dharma')->badge(),
                TextColumn::make('tanggal_mulai')->label('Mulai')->date('d/m/Y'),
                TextColumn::make('status_verifikasi')->label('Status')->badge(),
            ])
            ->headerActions([
                Action::make('tambah')
                    ->label('Tambah realisasi')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => RealisasiKegiatanResource::getUrl('create', ['kerja_sama_id' => $this->getOwnerRecord()->getKey()]))
                    ->visible(fn () => Gate::allows('create', RealisasiKegiatan::class)),
            ])
            ->recordActions([
                ViewAction::make()->url(fn ($record) => RealisasiKegiatanResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
