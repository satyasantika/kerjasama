<?php

namespace App\Http\Controllers;

use App\Actions\Pengguna\AkhiriMenyamar;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

class AkhiriMenyamarController extends Controller
{
    public function __invoke(AkhiriMenyamar $akhiri): RedirectResponse
    {
        abort_unless($akhiri->handle(), 403);

        return redirect()->to(Filament::getPanel('admin')->getUrl());
    }
}
