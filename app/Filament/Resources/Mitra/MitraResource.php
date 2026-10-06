<?php

namespace App\Filament\Resources\Mitra;

use App\Filament\Resources\Mitra\Pages\CreateMitra;
use App\Filament\Resources\Mitra\Pages\EditMitra;
use App\Filament\Resources\Mitra\Pages\ListMitra;
use App\Filament\Resources\Mitra\Schemas\MitraForm;
use App\Filament\Resources\Mitra\Tables\MitraTable;
use App\Models\Mitra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MitraResource extends Resource
{
    protected static ?string $model = Mitra::class;

    protected static ?string $modelLabel = 'mitra';

    protected static ?string $pluralModelLabel = 'mitra';

    protected static ?string $navigationLabel = 'Mitra';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return MitraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MitraTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMitra::route('/'),
            'create' => CreateMitra::route('/create'),
            'edit' => EditMitra::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
