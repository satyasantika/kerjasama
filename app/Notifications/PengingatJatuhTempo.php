<?php

namespace App\Notifications;

use App\Filament\Resources\KerjaSama\KerjaSamaResource;
use App\Models\KerjaSama;
use Filament\Actions\Action;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengingatJatuhTempo extends Notification
{
    public function __construct(public readonly KerjaSama $kerjaSama, public readonly int $ambangHari) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Kerja sama berakhir ≤ {$this->ambangHari} hari: {$this->kerjaSama->judul}")
            ->greeting('Pengingat masa berlaku kerja sama')
            ->line("Kerja sama \"{$this->kerjaSama->judul}\" akan berakhir pada {$this->kerjaSama->tanggal_berakhir->format('d/m/Y')} (sisa {$this->kerjaSama->sisa_hari} hari).")
            ->line('Mitra: '.$this->kerjaSama->mitra->pluck('nama')->implode(', '))
            ->action('Buka kerja sama', $this->url());
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return \Filament\Notifications\Notification::make()
            ->title("Kerja sama berakhir ≤ {$this->ambangHari} hari")
            ->body("{$this->kerjaSama->judul} berakhir {$this->kerjaSama->tanggal_berakhir->format('d/m/Y')} (sisa {$this->kerjaSama->sisa_hari} hari).")
            ->warning()
            ->actions([
                Action::make('buka')->label('Buka')->url($this->url()),
            ])
            ->getDatabaseMessage();
    }

    private function url(): string
    {
        return KerjaSamaResource::getUrl('view', ['record' => $this->kerjaSama], panel: 'admin');
    }
}
