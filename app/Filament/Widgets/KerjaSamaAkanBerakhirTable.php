<?php

namespace App\Filament\Widgets;

use App\Enums\StatusKerjaSama;
use App\Filament\Resources\KerjaSama\KerjaSamaResource;
use App\Models\KerjaSama;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class KerjaSamaAkanBerakhirTable extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->mengurusKerjaSama() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Akan berakhir dalam '.StatusKerjaSama::ambangAkanBerakhir().' hari')
            ->query(KerjaSama::query()->akanBerakhir()->with('mitra'))
            ->defaultSort('tanggal_berakhir')
            ->columns([
                TextColumn::make('judul')->label('Judul')->wrap()->limit(60)
                    ->url(fn (KerjaSama $record) => KerjaSamaResource::getUrl('view', ['record' => $record])),
                TextColumn::make('mitra.nama')->label('Mitra')->badge()->limitList(2),
                TextColumn::make('tanggal_berakhir')->label('Berakhir')->date('d/m/Y')->sortable(),
                TextColumn::make('sisa_hari')->label('Sisa')->suffix(' hari'),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Tidak ada kerja sama yang akan berakhir');
    }
}
