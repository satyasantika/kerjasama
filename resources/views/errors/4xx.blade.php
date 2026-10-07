@include('errors.layout', [
    'kode' => $exception->getStatusCode(),
    'judul' => 'Permintaan tidak dapat diproses',
    'pesan' => 'Alamat atau permintaan yang Anda kirim tidak dapat diproses. Silakan kembali dan coba lagi.',
])
