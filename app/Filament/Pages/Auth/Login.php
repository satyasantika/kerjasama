<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'Masuk ke sistem';
    }

    public function getSubHeading(): string|Htmlable|null
    {
        return 'Gunakan akun yang diberikan admin fakultas untuk mengelola dokumen dan realisasi kerja sama.';
    }
}
