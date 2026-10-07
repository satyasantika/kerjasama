<?php

return [
    // Unggah berkas dimatikan sementara: naskah dan bukti disimpan sebagai tautan Google Drive.
    // Aktifkan kembali dengan BERKAS_UNGGAH=true (isian unggah muncul berdampingan dengan tautan).
    'unggah_aktif' => (bool) env('BERKAS_UNGGAH', false),

    // Host tautan yang diterima dan boleh dituju route pembuka tautan (hindari open redirect).
    'host_diizinkan' => ['drive.google.com', 'docs.google.com'],
];
