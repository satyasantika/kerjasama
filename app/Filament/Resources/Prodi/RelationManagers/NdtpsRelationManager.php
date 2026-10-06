<?php

namespace App\Filament\Resources\Prodi\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class NdtpsRelationManager extends RelationManager
{
    protected static string $relationship = 'ndtps';

    protected static ?string $title = 'NDTPS per tahun (TS)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('tahun_ts')
                ->label('Tahun (TS)')
                ->numeric()
                ->required()
                ->minValue(2000)
                ->maxValue(2100)
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn (Unique $rule) => $rule->where('prodi_id', $this->getOwnerRecord()->getKey()),
                ),
            TextInput::make('ndtps')
                ->label('Jumlah dosen tetap (NDTPS)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(65535),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tahun_ts')
            ->columns([
                TextColumn::make('tahun_ts')->label('Tahun (TS)')->sortable(),
                TextColumn::make('ndtps')->label('NDTPS')->sortable(),
            ])
            ->defaultSort('tahun_ts', 'desc')
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make()]);
    }
}
