<?php

namespace App\Filament\Resources\RealisasiKegiatan\RelationManagers;

use App\Enums\JenisBukti;
use App\Models\BerkasBukti;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class BerkasBuktiRelationManager extends RelationManager
{
    protected static string $relationship = 'berkasBukti';

    protected static ?string $title = 'Bukti pelaksanaan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('jenis')
                ->label('Jenis bukti')
                ->options(JenisBukti::class)
                ->required()
                ->live(),
            FileUpload::make('path')
                ->label('Berkas')
                ->helperText('Foto: JPG/PNG maks. 5 MB. Lainnya: PDF maks. 10 MB.')
                ->required()
                ->disk('local')
                ->directory('bukti')
                ->visibility('private')
                ->storeFileNamesIn('nama_berkas')
                ->acceptedFileTypes(fn (Get $get): array => self::adalahFoto($get('jenis'))
                    ? ['image/jpeg', 'image/png'] : ['application/pdf'])
                ->maxSize(fn (Get $get): int => (int) (self::adalahFoto($get('jenis'))
                    ? config('kerjasama.maks_foto_kb') : config('kerjasama.maks_berkas_pdf_kb')))
                ->openable(false)
                ->downloadable(false)
                ->previewable(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_berkas')
            ->columns([
                TextColumn::make('nama_berkas')->label('Berkas')->searchable(),
                TextColumn::make('jenis')->label('Jenis')->badge(),
                TextColumn::make('ukuran_kb')->label('Ukuran')->suffix(' KB'),
                TextColumn::make('created_at')->label('Diunggah')->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('unduh')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('bukti.unduh', $record))
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ]);
    }

    /** Status Select bisa berupa string atau enum JenisBukti, tergantung tahap hidrasi. */
    private static function adalahFoto(mixed $jenis): bool
    {
        return ($jenis instanceof JenisBukti ? $jenis : JenisBukti::tryFrom((string) $jenis)) === JenisBukti::Foto;
    }

    protected function canCreate(): bool
    {
        return Gate::allows('create', BerkasBukti::class)
            && Gate::allows('update', $this->getOwnerRecord());
    }

    protected function canDelete(Model $record): bool
    {
        return Gate::allows('delete', $record);
    }
}
