<?php

namespace App\Filament\Resources\RealisasiKegiatan\Actions;

use App\Models\RealisasiKegiatan;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

/** Aksi alur verifikasi: pengusul mengajukan → admin fakultas memverifikasi atau menolak. */
class AksiVerifikasi
{
    public static function ajukan(): Action
    {
        return Action::make('ajukan')
            ->label('Ajukan verifikasi')
            ->icon('heroicon-o-paper-airplane')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (RealisasiKegiatan $record) => auth()->user()?->can('ajukan', $record) ?? false)
            ->action(fn (RealisasiKegiatan $record) => self::jalankan(fn () => $record->ajukan(), 'Realisasi diajukan untuk verifikasi.'));
    }

    public static function verifikasi(): Action
    {
        return Action::make('verifikasi')
            ->label('Verifikasi')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (RealisasiKegiatan $record) => auth()->user()?->can('verifikasi', $record) ?? false)
            ->action(fn (RealisasiKegiatan $record) => self::jalankan(fn () => $record->verifikasi(auth()->user()), 'Realisasi terverifikasi.'));
    }

    public static function tolak(): Action
    {
        return Action::make('tolak')
            ->label('Tolak')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->schema([
                Textarea::make('catatan')->label('Catatan penolakan')->required(),
            ])
            ->visible(fn (RealisasiKegiatan $record) => auth()->user()?->can('verifikasi', $record) ?? false)
            ->action(fn (RealisasiKegiatan $record, array $data) => self::jalankan(fn () => $record->tolak(auth()->user(), $data['catatan']), 'Realisasi ditolak.'));
    }

    private static function jalankan(callable $aksi, string $pesanBerhasil): void
    {
        try {
            $aksi();
            Notification::make()->title($pesanBerhasil)->success()->send();
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }
}
