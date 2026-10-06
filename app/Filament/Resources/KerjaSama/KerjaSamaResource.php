<?php

namespace App\Filament\Resources\KerjaSama;

use App\Filament\Resources\KerjaSama\Pages\CreateKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\EditKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\ListKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\ViewKerjaSama;
use App\Filament\Resources\KerjaSama\RelationManagers\AnakRelationManager;
use App\Filament\Resources\KerjaSama\RelationManagers\RealisasiRelationManager;
use App\Filament\Resources\KerjaSama\Schemas\KerjaSamaForm;
use App\Filament\Resources\KerjaSama\Schemas\KerjaSamaInfolist;
use App\Filament\Resources\KerjaSama\Tables\KerjaSamaTable;
use App\Models\KerjaSama;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KerjaSamaResource extends Resource
{
    protected static ?string $model = KerjaSama::class;

    protected static ?string $slug = 'kerja-sama';

    protected static ?string $modelLabel = 'kerja sama';

    protected static ?string $pluralModelLabel = 'kerja sama';

    protected static ?string $navigationLabel = 'Kerja Sama';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'judul';

    public static function form(Schema $schema): Schema
    {
        return KerjaSamaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KerjaSamaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KerjaSamaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [RealisasiRelationManager::class, AnakRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKerjaSama::route('/'),
            'create' => CreateKerjaSama::route('/create'),
            'view' => ViewKerjaSama::route('/{record}'),
            'edit' => EditKerjaSama::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['mitra', 'prodi', 'induk']);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
