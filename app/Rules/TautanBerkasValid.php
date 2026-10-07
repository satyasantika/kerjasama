<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Tautan berkas harus https, tanpa kredensial, dan berhost daftar putih config/berkas.php. */
class TautanBerkasValid implements ValidationRule
{
    public static function sah(?string $url): bool
    {
        if (blank($url) || strlen($url) > 500) {
            return false;
        }

        $bagian = parse_url($url);

        return $bagian !== false
            && ($bagian['scheme'] ?? null) === 'https'
            && ! isset($bagian['user'], $bagian['pass'])
            && in_array(strtolower($bagian['host'] ?? ''), config('berkas.host_diizinkan'), true);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::sah((string) $value)) {
            $fail('Tautan harus berupa alamat https Google Drive/Docs (drive.google.com atau docs.google.com), maksimal 500 karakter.');
        }
    }
}
