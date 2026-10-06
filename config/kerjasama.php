<?php

return [
    // Ambang hari status "akan berakhir" — ASUMSI README §7, belum dikonfirmasi.
    'ambang_akan_berakhir' => (int) env('KERJASAMA_AMBANG_AKAN_BERAKHIR', 90),

    // Batas unggahan (KB)
    'maks_berkas_pdf_kb' => 10240,
    'maks_foto_kb' => 5120,
];
