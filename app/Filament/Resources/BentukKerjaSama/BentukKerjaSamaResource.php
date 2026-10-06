<?php

namespace App\Filament\Resources\BentukKerjaSama;

use App\Filament\Resources\BentukKerjaSama\Pages\CreateBentukKerjaSama;
use App\Filament\Resources\BentukKerjaSama\Pages\EditBentukKerjaSama;
use App\Filament\Resources\BentukKerjaSama\Pages\ListBentukKerjaSama;
use App\Filament\Resources\BentukKerjaSama\Schemas\BentukKerjaSamaForm;
use App\Filament\Resources\BentukKerjaSama\Tables\BentukKerjaSamaTable;
use App\Models\BentukKerjaSama;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BentukKerjaSamaResource extends Resource
{
    protected static ?string $model = BentukKerjaSama::class;

    protected static ?string $modelLabel = 'bentuk kerja sama';

    protected static ?string $pluralModelLabel = 'bentuk kerja sama';

    protected static ?string $navigationLabel = 'Bentuk Kerja Sama';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return BentukKerjaSamaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BentukKerjaSamaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBentukKerjaSama::route('/'),
            'create' => CreateBentukKerjaSama::route('/create'),
            'edit' => EditBentukKerjaSama::route('/{record}/edit'),
        ];
    }
}
