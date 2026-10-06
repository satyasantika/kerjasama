<?php

namespace App\Filament\Resources\Prodi;

use App\Filament\Resources\Prodi\Pages\CreateProdi;
use App\Filament\Resources\Prodi\Pages\EditProdi;
use App\Filament\Resources\Prodi\Pages\ListProdi;
use App\Filament\Resources\Prodi\RelationManagers\NdtpsRelationManager;
use App\Filament\Resources\Prodi\Schemas\ProdiForm;
use App\Filament\Resources\Prodi\Tables\ProdiTable;
use App\Models\Prodi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProdiResource extends Resource
{
    protected static ?string $model = Prodi::class;

    protected static ?string $slug = 'prodi';

    protected static ?string $modelLabel = 'program studi';

    protected static ?string $pluralModelLabel = 'program studi';

    protected static ?string $navigationLabel = 'Program Studi';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return ProdiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProdiTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [NdtpsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProdi::route('/'),
            'create' => CreateProdi::route('/create'),
            'edit' => EditProdi::route('/{record}/edit'),
        ];
    }
}
