<?php

use Symfony\Component\Finder\Finder;

// STANDAR-TEKNIS §2.4: aplikasi dipasang di subpath (/kerjasama), jadi URL
// berawalan "/" di Blade, JS, dan panduan statis akan lepas dari subpath.
it('tidak memakai URL absolut berawalan slash di view, JS, dan panduan', function () {
    $berkas = Finder::create()
        ->files()
        ->in([base_path('resources/views'), base_path('resources/js'), base_path('public/panduan')])
        ->name(['*.php', '*.js', '*.html']);

    $pelanggaran = [];

    foreach ($berkas as $f) {
        $isi = $f->getContents();

        if (preg_match_all('#(?:href|src|action)=["\']/(?!/)|fetch\(\s*[\'"`]/(?!/)#', $isi, $m)) {
            $pelanggaran[] = $f->getRelativePathname();
        }
    }

    expect($pelanggaran)->toBe([]);
});
