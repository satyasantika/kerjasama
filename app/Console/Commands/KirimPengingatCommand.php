<?php

namespace App\Console\Commands;

use App\Services\PengingatJatuhTempo;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('kerjasama:kirim-pengingat')]
#[Description('Kirim pengingat kerja sama yang mendekati jatuh tempo (ambang di config/kerjasama.php)')]
class KirimPengingatCommand extends Command
{
    public function handle(PengingatJatuhTempo $pengingat): int
    {
        $jumlah = $pengingat->kirim();

        $this->info("Pengingat terkirim untuk {$jumlah} kerja sama.");

        return self::SUCCESS;
    }
}
