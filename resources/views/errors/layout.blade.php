@php
    $kode = (int) ($kode ?? 500);
    $judul = $judul ?? 'Terjadi kesalahan';
    $pesan = $pesan ?? 'Maaf, terjadi kendala saat memproses permintaan Anda.';
    $boleh_kembali = $boleh_kembali ?? true;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $kode }} · {{ $judul }} — Kerja Sama FKIP</title>
    <link rel="icon" href="{{ url('/favicon.ico') }}">
    <link rel="stylesheet" href="{{ url('/fonts/filament/filament/inter/index.css') }}">
    @include('partials.tema-skrip')
    @include('partials.tema-gaya')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; background: var(--kertas); color: var(--tinta); font: 16px/1.65 var(--sans); -webkit-font-smoothing: antialiased; }
        a { color: inherit; }
        header { display: flex; align-items: center; justify-content: space-between; padding: 0 clamp(16px, 4vw, 40px); height: 64px; border-bottom: 1px solid var(--garis); }
        .merek { font-weight: 800; letter-spacing: -.02em; font-size: 18px; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .merek i { width: 26px; height: 26px; border-radius: 7px; background: var(--amber); display: grid; place-items: center; }
        .merek svg { width: 16px; height: 16px; }
        main { flex: 1; display: grid; place-items: center; padding: 48px 20px; position: relative; overflow: hidden; }
        main::before { content: ""; position: absolute; inset: 0; background-image: linear-gradient(var(--garis) 1px, transparent 1px), linear-gradient(90deg, var(--garis) 1px, transparent 1px); background-size: 48px 48px; opacity: .45; mask-image: radial-gradient(ellipse 60% 55% at 50% 45%, #000, transparent 75%); pointer-events: none; }
        .isi { position: relative; width: min(560px, 100%); text-align: center; }
        .kode { font: 400 clamp(96px, 22vw, 168px)/1 var(--serif); letter-spacing: -.04em; color: var(--amber-tua); margin: 0; }
        .kode span { display: inline-block; }
        .label { display: inline-flex; gap: 8px; align-items: center; font-size: 13px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--amber-tua); margin-top: 8px; }
        .label::before, .label::after { content: ""; width: 24px; height: 2px; background: currentColor; }
        h1 { font: 400 clamp(28px, 5vw, 38px)/1.15 var(--serif); letter-spacing: -.015em; margin: 14px 0 12px; }
        p { color: var(--tinta-2); margin: 0 auto 32px; max-width: 46ch; }
        .aksi { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .tombol { display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; border-radius: 10px; font: 600 15px var(--sans); text-decoration: none; border: 1.5px solid transparent; cursor: pointer; transition: transform .15s, background .15s, color .15s; }
        .tombol svg { width: 18px; height: 18px; }
        .tombol.utama { background: var(--amber); color: #1c1917; box-shadow: 0 1px 0 #b45309, 0 6px 16px -6px rgba(245, 158, 11, .7); }
        .tombol.utama:hover { transform: translateY(-1px); background: #fbbf24; }
        .tombol.garis { background: transparent; color: var(--tinta); border-color: var(--tinta); }
        .tombol.garis:hover { background: var(--tinta); color: var(--kertas); }
        .tombol:focus-visible { outline: 2px solid var(--amber); outline-offset: 3px; }
        footer { padding: 24px; text-align: center; font-size: 13.5px; color: var(--tinta-2); border-top: 1px solid var(--garis); }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<header>
    <a class="merek" href="{{ url('/') }}">
        <i aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="#1c1917" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="m9 14 2 2 4-4"/></svg></i>
        Kerja Sama FKIP
    </a>
    @include('partials.sakelar-tema')
</header>

<main>
    <div class="isi" role="alert">
        <p class="kode" aria-hidden="true" style="margin:0 auto"><span>{{ $kode }}</span></p>
        <span class="label">Kesalahan {{ $kode }}</span>
        <h1>{{ $judul }}</h1>
        <p>{{ $pesan }}</p>
        <div class="aksi">
            @if ($boleh_kembali)
                <a class="tombol garis" href="{{ url('/') }}" data-kembali>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Kembali
                </a>
            @endif
            <a class="tombol utama" href="{{ url('/') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                Beranda
            </a>
        </div>
    </div>
</main>

<footer>© {{ date('Y') }} Fakultas Keguruan dan Ilmu Pendidikan, Universitas Siliwangi</footer>

<script>
    document.querySelectorAll('[data-kembali]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            if (window.history.length > 1) { e.preventDefault(); window.history.back(); }
        });
    });
</script>
</body>
</html>
