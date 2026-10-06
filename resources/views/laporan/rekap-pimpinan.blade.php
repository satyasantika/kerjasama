<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Kerja Sama FKIP</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 16px 0 6px; border-bottom: 1px solid #999; padding-bottom: 2px; }
        .sub { color: #666; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { border: 1px solid #bbb; padding: 3px 5px; text-align: left; vertical-align: top; }
        th { background: #eee; }
        td.angka, th.angka { text-align: right; }
        .kartu td { text-align: center; font-size: 14px; font-weight: bold; }
        .kartu th { text-align: center; }
        .kosong { color: #777; font-style: italic; }
    </style>
</head>
<body>
    <h1>Rekap Kerja Sama FKIP Unsil</h1>
    <div class="sub">Dicetak {{ $dicetak->format('d/m/Y H:i') }} · ambang "akan berakhir": {{ $ambang }} hari</div>

    <h2>Ringkasan status</h2>
    <table class="kartu">
        <tr>@foreach ($status as $label => $jumlah)<th>{{ $label }}</th>@endforeach</tr>
        <tr>@foreach ($status as $jumlah)<td>{{ $jumlah }}</td>@endforeach</tr>
    </table>

    <h2>Kerja sama berlaku per tingkat</h2>
    <table>
        <tr><th>Tingkat</th><th class="angka">Jumlah</th></tr>
        @foreach ($perTingkat as $label => $jumlah)<tr><td>{{ $label }}</td><td class="angka">{{ $jumlah }}</td></tr>@endforeach
    </table>

    <h2>Kerja sama berlaku per jenis mitra</h2>
    <table>
        <tr><th>Jenis mitra</th><th class="angka">Jumlah</th></tr>
        @foreach ($perJenisMitra as $label => $jumlah)<tr><td>{{ $label }}</td><td class="angka">{{ $jumlah }}</td></tr>@endforeach
    </table>
    <div class="sub">Kerja sama dengan beberapa mitra dihitung pada tiap jenis mitranya.</div>

    @foreach (['Akan berakhir dalam '.$ambang.' hari' => $akanBerakhir, 'Sudah kedaluwarsa' => $kedaluwarsa] as $judul => $daftar)
        <h2>{{ $judul }}</h2>
        @if ($daftar->isEmpty())
            <p class="kosong">Tidak ada.</p>
        @else
            <table>
                <tr><th>Judul</th><th>Mitra</th><th>Prodi</th><th>Berakhir</th></tr>
                @foreach ($daftar as $ks)
                    <tr>
                        <td>{{ $ks->judul }}</td>
                        <td>{{ $ks->mitra->pluck('nama')->implode('; ') }}</td>
                        <td>{{ $ks->prodi->pluck('nama')->implode('; ') ?: '—' }}</td>
                        <td>{{ $ks->tanggal_berakhir->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endforeach
</body>
</html>
