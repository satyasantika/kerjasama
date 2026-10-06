<?php

return [
    // Ambang hari status "akan berakhir" — ASUMSI README §7, belum dikonfirmasi.
    'ambang_akan_berakhir' => (int) env('KERJASAMA_AMBANG_AKAN_BERAKHIR', 90),

    // Ambang pengingat jatuh tempo (hari sebelum berakhir) — ASUMSI README §8 fase 2.
    'ambang_pengingat' => [180, 90, 30],

    // Batas unggahan (KB)
    'maks_berkas_pdf_kb' => 10240,
    'maks_foto_kb' => 5120,
];
