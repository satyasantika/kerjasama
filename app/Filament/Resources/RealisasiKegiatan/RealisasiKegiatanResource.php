<?php

namespace App\Filament\Resources\RealisasiKegiatan;

use App\Filament\Resources\RealisasiKegiatan\Pages\CreateRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\Pages\EditRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\Pages\ListRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\Pages\ViewRealisasiKegiatan;
use App\Filament\Resources\RealisasiKegiatan\RelationManagers\BerkasBuktiRelationManager;
use App\Filament\Resources\RealisasiKegiatan\Schemas\RealisasiKegiatanForm;
use App\Filament\Resources\RealisasiKegiatan\Schemas\RealisasiKegiatanInfolist;
use App\Filament\Resources\RealisasiKegiatan\Tables\RealisasiKegiatanTable;
use App\Models\RealisasiKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RealisasiKegiatanResource extends Resource
{
    protected static ?string $model = RealisasiKegiatan::class;

    protected static ?string $slug = 'realisasi-kegiatan';

    protected static ?string $modelLabel = 'realisasi kegiatan';

    protected static ?string $pluralModelLabel = 'realisasi kegiatan';

    protected static ?string $navigationLabel = 'Realisasi Kegiatan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'judul_kegiatan';

    public static function form(Schema $schema): Schema
    {
        return RealisasiKegiatanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RealisasiKegiatanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RealisasiKegiatanTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [BerkasBuktiRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRealisasiKegiatan::route('/'),
            'create' => CreateRealisasiKegiatan::route('/create'),
            'view' => ViewRealisasiKegiatan::route('/{record}'),
            'edit' => EditRealisasiKegiatan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['kerjaSama', 'prodi']);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
