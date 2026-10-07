@include('errors.layout', [
    'kode' => $exception->getStatusCode(),
    'judul' => 'Terjadi kesalahan pada sistem',
    'pesan' => 'Maaf, ada kendala di sisi kami. Coba lagi beberapa saat lagi.',
])
