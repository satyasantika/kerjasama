<?php

namespace App\Filament\Resources\KerjaSama\RelationManagers;

use App\Enums\RekomendasiEvaluasi;
use App\Models\EvaluasiKerjaSama;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class EvaluasiRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluasi';

    protected static ?string $title = 'Evaluasi keefektifan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tahun_ts')->label('Tahun (TS)')->numeric()->required()->minValue(2000)->maxValue(2100)->default(now()->year),
            Select::make('skor_keefektifan')
                ->label('Skor keefektifan')
                ->options(EvaluasiKerjaSama::DESKRIPTOR)
                ->required()
                ->helperText('Deskriptor LAMDIK IAPSK 3.0 indikator (b).'),
            Textarea::make('analisis')->label('Analisis')->required()->rows(4)->columnSpanFull(),
            Select::make('rekomendasi')->label('Rekomendasi')->options(RekomendasiEvaluasi::class)->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tahun_ts')
            ->columns([
                TextColumn::make('tahun_ts')->label('TS')->sortable(),
                TextColumn::make('skor_keefektifan')->label('Skor')->badge(),
                TextColumn::make('rekomendasi')->label('Rekomendasi')->badge(),
                TextColumn::make('analisis')->label('Analisis')->limit(60)->wrap(),
                TextColumn::make('penilai.name')->label('Dinilai oleh'),
            ])
            ->defaultSort('tahun_ts', 'desc')
            ->headerActions([
                CreateAction::make()->mutateDataUsing(fn (array $data) => $data + ['dinilai_oleh' => auth()->id()]),
            ])
            ->recordActions([EditAction::make()]);
    }

    protected function canCreate(): bool
    {
        return Gate::allows('create', EvaluasiKerjaSama::class)
            && Gate::allows('update', $this->getOwnerRecord());
    }

    protected function canEdit(Model $record): bool
    {
        return Gate::allows('update', $record);
    }

    protected function canDelete(Model $record): bool
    {
        return false;
    }
}
