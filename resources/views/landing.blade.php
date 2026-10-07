<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Kerja Sama FKIP Universitas Siliwangi</title>
    <meta name="description" content="Pengelolaan dokumen kerja sama (MoU, MoA, PKS, IA) FKIP Universitas Siliwangi: masa berlaku, realisasi kegiatan, verifikasi, dan data akreditasi LAMDIK IAPSK 3.0 dalam satu sistem.">
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/fonts/filament/filament/inter/index.css">
    <style>
        :root {
            --kertas: #fbf8f2;
            --kertas-2: #f3eee3;
            --tinta: #1c1917;
            --tinta-2: #57534e;
            --garis: #e4dccb;
            --amber: #f59e0b;
            --amber-tua: #b45309;
            --amber-muda: #fef3c7;
            --hijau: #15803d;
            --merah: #b91c1c;
            --serif: "Iowan Old Style", "Palatino Linotype", Palatino, "Book Antiqua", Georgia, serif;
            --sans: "Inter Variable", "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --kertas: #17140f;
                --kertas-2: #211c14;
                --tinta: #f5efe3;
                --tinta-2: #b8ae9c;
                --garis: #3a3224;
                --amber-muda: #3a2a08;
                --amber-tua: #fbbf24;
            }
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--kertas); color: var(--tinta); font: 16px/1.65 var(--sans); -webkit-font-smoothing: antialiased; }
        img { max-width: 100%; display: block; }
        a { color: inherit; }
        .wrap { width: min(1160px, 100% - 48px); margin-inline: auto; }
        .serif { font-family: var(--serif); }

        /* Bilah atas */
        header.atas { position: sticky; top: 0; z-index: 20; background: color-mix(in srgb, var(--kertas) 88%, transparent); backdrop-filter: blur(10px); border-bottom: 1px solid var(--garis); }
        header.atas .wrap { display: flex; align-items: center; gap: 28px; height: 64px; }
        .merek { font-weight: 800; letter-spacing: -.02em; font-size: 18px; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .merek i { width: 26px; height: 26px; border-radius: 7px; background: var(--amber); display: grid; place-items: center; }
        .merek svg { width: 16px; height: 16px; }
        nav.menu { display: flex; gap: 24px; margin-left: auto; font-size: 14.5px; font-weight: 500; }
        nav.menu a { text-decoration: none; color: var(--tinta-2); }
        nav.menu a:hover { color: var(--tinta); }
        .tombol { display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; border-radius: 10px; font-weight: 600; font-size: 15px; text-decoration: none; border: 1.5px solid transparent; transition: transform .15s, box-shadow .15s, background .15s; }
        .tombol.utama { background: var(--amber); color: #1c1917; box-shadow: 0 1px 0 #b45309, 0 6px 16px -6px rgba(245, 158, 11, .7); }
        .tombol.utama:hover { transform: translateY(-1px); background: #fbbf24; }
        .tombol.garis { border-color: var(--tinta); }
        .tombol.garis:hover { background: var(--tinta); color: var(--kertas); }
        header.atas .tombol { padding: 8px 16px; font-size: 14px; }

        /* Hero */
        .hero { padding: 72px 0 40px; position: relative; overflow: hidden; }
        .hero::before { content: ""; position: absolute; inset: 0; background-image: linear-gradient(var(--garis) 1px, transparent 1px), linear-gradient(90deg, var(--garis) 1px, transparent 1px); background-size: 48px 48px; opacity: .45; mask-image: radial-gradient(ellipse 70% 60% at 70% 30%, #000, transparent 75%); pointer-events: none; }
        .hero .wrap { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 48px; }
        .label { display: inline-flex; gap: 8px; align-items: center; font-size: 13px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--amber-tua); }
        .label::before { content: ""; width: 28px; height: 2px; background: currentColor; }
        h1 { font: 400 clamp(38px, 6vw, 68px)/1.05 var(--serif); letter-spacing: -.02em; margin: 18px 0 20px; max-width: 16ch; }
        h1 em { font-style: italic; color: var(--amber-tua); }
        .lead { font-size: 19px; color: var(--tinta-2); max-width: 54ch; margin: 0 0 32px; }
        .aksi { display: flex; flex-wrap: wrap; gap: 12px; }
        .fakta { display: flex; flex-wrap: wrap; gap: 10px 28px; margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--garis); font-size: 14px; color: var(--tinta-2); }
        .fakta b { color: var(--tinta); font-weight: 700; }
        .bingkai { border: 1px solid var(--garis); border-radius: 14px; background: var(--kertas-2); box-shadow: 0 30px 60px -30px rgba(120, 80, 10, .35), 0 2px 0 var(--garis); overflow: hidden; }
        .bingkai .bar { height: 34px; display: flex; align-items: center; gap: 7px; padding: 0 14px; border-bottom: 1px solid var(--garis); background: var(--kertas); }
        .bingkai .bar i { width: 11px; height: 11px; border-radius: 50%; background: var(--garis); }
        .bingkai .bar span { margin-left: 10px; font-size: 12px; color: var(--tinta-2); background: var(--kertas-2); padding: 3px 14px; border-radius: 6px; }
        .bingkai img { width: 100%; }
        @media (min-width: 960px) {
            .hero .wrap { grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); align-items: center; }
            .hero .bingkai { margin-right: -60px; }
        }

        /* Seksi umum */
        section { padding: 88px 0; }
        section.gelap { background: var(--kertas-2); border-block: 1px solid var(--garis); }
        h2 { font: 400 clamp(30px, 4vw, 44px)/1.12 var(--serif); letter-spacing: -.015em; margin: 14px 0 14px; max-width: 22ch; }
        .pengantar { color: var(--tinta-2); max-width: 60ch; font-size: 17px; margin: 0 0 48px; }

        /* Fitur */
        .fitur { display: grid; gap: 1px; background: var(--garis); border: 1px solid var(--garis); border-radius: 16px; overflow: hidden; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
        .fitur article { background: var(--kertas); padding: 32px 28px; }
        .fitur .no { font: 700 13px var(--sans); color: var(--amber-tua); letter-spacing: .1em; }
        .fitur h3 { margin: 10px 0 8px; font-size: 19px; letter-spacing: -.01em; }
        .fitur p { margin: 0; color: var(--tinta-2); font-size: 15px; }

        /* Status */
        .status { display: grid; gap: 40px; align-items: center; }
        @media (min-width: 960px) { .status { grid-template-columns: 1fr 1fr; } }
        .lencana { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12.5px; font-weight: 600; border: 1px solid; }
        .lencana.hijau { color: var(--hijau); background: #dcfce7; border-color: #86efac; }
        .lencana.kuning { color: #a16207; background: #fef9c3; border-color: #fde047; }
        .lencana.merah { color: var(--merah); background: #fee2e2; border-color: #fca5a5; }
        .lencana.abu { color: #44403c; background: #e7e5e4; border-color: #d6d3d1; }
        .lencana.biru { color: #1d4ed8; background: #dbeafe; border-color: #93c5fd; }
        .garis-waktu { background: var(--kertas); border: 1px solid var(--garis); border-radius: 16px; padding: 28px; }
        .garis-waktu .baris { display: grid; grid-template-columns: 120px 1fr; gap: 16px; align-items: center; padding: 14px 0; border-bottom: 1px dashed var(--garis); }
        .garis-waktu .baris:last-child { border: 0; }
        .garis-waktu .rel { position: relative; height: 12px; background: var(--kertas-2); border-radius: 6px; border: 1px solid var(--garis); }
        .garis-waktu .rel b { position: absolute; top: -1px; bottom: -1px; border-radius: 6px; }
        .garis-waktu small { color: var(--tinta-2); font-size: 12px; }
        .hari { display: flex; gap: 12px; margin-top: 22px; flex-wrap: wrap; }
        .hari div { flex: 1 1 90px; text-align: center; background: var(--amber-muda); border-radius: 12px; padding: 14px 8px; }
        .hari strong { display: block; font: 400 34px/1 var(--serif); color: var(--amber-tua); }
        .hari span { font-size: 12.5px; color: var(--tinta-2); }

        /* Alur */
        .alur { display: grid; gap: 0; counter-reset: langkah; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); }
        .alur li { list-style: none; position: relative; padding: 0 28px 0 0; }
        .alur li::before { counter-increment: langkah; content: counter(langkah); display: grid; place-items: center; width: 44px; height: 44px; border-radius: 50%; background: var(--tinta); color: var(--kertas); font: 400 22px var(--serif); margin-bottom: 18px; position: relative; z-index: 1; }
        .alur li::after { content: ""; position: absolute; left: 44px; right: 0; top: 22px; border-top: 2px dashed var(--garis); }
        .alur li:last-child::after { display: none; }
        .alur h3 { margin: 0 0 6px; font-size: 18px; }
        .alur p { margin: 0; color: var(--tinta-2); font-size: 15px; }
        ol.alur { padding: 0; margin: 0; }

        /* Peran */
        .peran { display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }
        .peran a { display: flex; flex-direction: column; gap: 10px; text-decoration: none; background: var(--kertas); border: 1px solid var(--garis); border-radius: 16px; padding: 26px 24px; transition: transform .18s, box-shadow .18s, border-color .18s; }
        .peran a:hover { transform: translateY(-4px); border-color: var(--amber); box-shadow: 0 18px 30px -18px rgba(180, 83, 9, .5); }
        .peran h3 { margin: 6px 0 0; font-size: 20px; }
        .peran p { margin: 0; font-size: 14.5px; color: var(--tinta-2); flex: 1; }
        .peran .siapa { font-size: 12.5px; color: var(--tinta-2); }
        .peran .menuju { font-weight: 600; font-size: 14px; color: var(--amber-tua); }
        .peran ul { margin: 0; padding-left: 18px; font-size: 14px; color: var(--tinta-2); }

        /* Akreditasi */
        .dua { display: grid; gap: 48px; align-items: center; }
        @media (min-width: 960px) { .dua { grid-template-columns: 1fr 1fr; } }
        .centang { list-style: none; padding: 0; margin: 24px 0 0; display: grid; gap: 14px; }
        .centang li { display: grid; grid-template-columns: 24px 1fr; gap: 12px; font-size: 16px; }
        .centang svg { width: 22px; height: 22px; color: var(--hijau); margin-top: 2px; }

        /* Penutup */
        .penutup { background: var(--tinta); color: var(--kertas); text-align: center; }
        .penutup h2 { margin-inline: auto; color: var(--kertas); }
        .penutup p { color: #d6d3d1; max-width: 52ch; margin: 0 auto 32px; }
        .penutup .aksi { justify-content: center; }
        .penutup .tombol.garis { border-color: #78716c; color: var(--kertas); }
        .penutup .tombol.garis:hover { background: var(--kertas); color: #1c1917; }
        footer { padding: 36px 0; font-size: 14px; color: var(--tinta-2); border-top: 1px solid var(--garis); }
        footer .wrap { display: flex; flex-wrap: wrap; gap: 16px 32px; justify-content: space-between; }
        footer a { color: var(--tinta-2); }

        @media (max-width: 720px) {
            nav.menu a:not(.tombol) { display: none; }
            nav.menu { margin-left: auto; }
            section { padding: 64px 0; }
            .hero { padding-top: 40px; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } html { scroll-behavior: auto; } }
    </style>
</head>
<body>

<header class="atas">
    <div class="wrap">
        <a class="merek" href="/">
            <i aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="#1c1917" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="m9 14 2 2 4-4"/></svg></i>
            Kerja Sama FKIP
        </a>
        <nav class="menu" aria-label="Menu utama">
            <a href="#fitur">Fitur</a>
            <a href="#masa-berlaku">Masa berlaku</a>
            <a href="#peran">Peran</a>
            <a href="/panduan/">Panduan</a>
            <a class="tombol utama" href="/admin">{{ auth()->check() ? 'Buka dasbor' : 'Masuk' }}</a>
        </nav>
    </div>
</header>

<main>
    {{-- HERO --}}
    <div class="hero">
        <div class="wrap">
            <div>
                <span class="label">FKIP Universitas Siliwangi</span>
                <h1>Setiap perjanjian, <em>tercatat</em> dan terpantau.</h1>
                <p class="lead">Satu tempat untuk menyimpan MoU, MoA, PKS, dan IA bersama mitra, memantau masa berlakunya, mencatat kegiatan yang sudah terlaksana, dan menyiapkan data akreditasi LAMDIK IAPSK 3.0.</p>
                <div class="aksi">
                    <a class="tombol utama" href="/admin">{{ auth()->check() ? 'Buka dasbor' : 'Masuk ke sistem' }} →</a>
                    <a class="tombol garis" href="/panduan/">Baca panduan pengguna</a>
                </div>
                <div class="fakta">
                    <span><b>4</b> jenis dokumen</span>
                    <span><b>13</b> program studi</span>
                    <span><b>4</b> peran pengguna</span>
                    <span>Pengingat <b>180 · 90 · 30</b> hari</span>
                </div>
            </div>
            <div class="bingkai" aria-hidden="false">
                <div class="bar"><i></i><i></i><i></i><span>Dasbor · Kerja Sama FKIP</span></div>
                <img src="/panduan/img/landing-dasbor.png" width="1680" height="1000" alt="Dasbor sistem: ringkasan kerja sama aktif, akan berakhir, dan kedaluwarsa, disertai grafik per tingkat dan jenis mitra." fetchpriority="high">
            </div>
        </div>
    </div>

    {{-- FITUR --}}
    <section id="fitur">
        <div class="wrap">
            <span class="label">Yang dikerjakan sistem</span>
            <h2>Dari naskah perjanjian sampai angka akreditasi</h2>
            <p class="pengantar">Dirancang mengikuti cara kerja urusan kerja sama fakultas, dengan istilah dan alur yang sama seperti di lapangan.</p>
            <div class="fitur">
                <article>
                    <span class="no">01</span>
                    <h3>Arsip dokumen terpusat</h3>
                    <p>Naskah PDF, nomor, penandatangan, dan para pihak tersimpan terstruktur. Relasi induk–anak menunjukkan MoU yang menaungi MoA, PKS, dan IA.</p>
                </article>
                <article>
                    <span class="no">02</span>
                    <h3>Status dihitung otomatis</h3>
                    <p>Aktif, akan berakhir, atau kedaluwarsa ditentukan dari tanggal, bukan diketik manual, sehingga angka di dasbor selalu mengikuti hari ini.</p>
                </article>
                <article>
                    <span class="no">03</span>
                    <h3>Pengingat jatuh tempo</h3>
                    <p>Notifikasi dan surel dikirim 180, 90, dan 30 hari sebelum dokumen berakhir, agar perpanjangan tidak terlambat.</p>
                </article>
                <article>
                    <span class="no">04</span>
                    <h3>Realisasi dengan verifikasi</h3>
                    <p>Prodi melaporkan kegiatan beserta bukti (laporan, daftar hadir, foto). Admin fakultas memverifikasi atau mengembalikan dengan catatan.</p>
                </article>
                <article>
                    <span class="no">05</span>
                    <h3>Data siap akreditasi</h3>
                    <p>Ekspor Excel Tabel Kerja Sama Tridharma per prodi, serta simulasi skor elemen kerja sama LAMDIK IAPSK 3.0.</p>
                </article>
                <article>
                    <span class="no">06</span>
                    <h3>Akses sesuai peran</h3>
                    <p>Admin prodi hanya mengubah data prodinya, pimpinan hanya membaca. Pembatasan ditegakkan di sisi server, bukan sekadar menyembunyikan tombol.</p>
                </article>
            </div>
        </div>
    </section>

    {{-- MASA BERLAKU --}}
    <section id="masa-berlaku" class="gelap">
        <div class="wrap status">
            <div>
                <span class="label">Pemantauan</span>
                <h2>Tahu mana yang perlu diperpanjang, jauh hari</h2>
                <p class="pengantar" style="margin-bottom:20px">Setiap dokumen punya satu status yang dihitung dari tanggal mulai dan berakhirnya. Dasbor dan daftar kerja sama dapat disaring menurut status, tingkat, jenis mitra, prodi, dan tahun.</p>
                <p style="margin:0 0 8px"><span class="lencana hijau">Aktif</span> <span class="lencana kuning">Akan berakhir</span> <span class="lencana merah">Kedaluwarsa</span> <span class="lencana biru">Belum berlaku</span> <span class="lencana abu">Dihentikan</span></p>
                <div class="hari" aria-label="Jadwal pengingat">
                    <div><strong>180</strong><span>hari sebelum</span></div>
                    <div><strong>90</strong><span>hari sebelum</span></div>
                    <div><strong>30</strong><span>hari sebelum</span></div>
                </div>
            </div>
            <div class="garis-waktu" role="img" aria-label="Contoh garis waktu masa berlaku empat dokumen, dari aktif hingga kedaluwarsa">
                <div class="baris"><div><b>MoU</b><br><small>Mitra luar negeri</small></div><div class="rel"><b style="left:2%;width:88%;background:#86efac"></b></div></div>
                <div class="baris"><div><b>MoA</b><br><small>Sekolah mitra</small></div><div class="rel"><b style="left:14%;width:70%;background:#86efac"></b></div></div>
                <div class="baris"><div><b>PKS</b><br><small>Organisasi profesi</small></div><div class="rel"><b style="left:6%;width:50%;background:#fde047"></b></div></div>
                <div class="baris"><div><b>MoA</b><br><small>Pemerintah daerah</small></div><div class="rel"><b style="left:0;width:34%;background:#fca5a5"></b></div></div>
            </div>
        </div>
    </section>

    {{-- ALUR --}}
    <section id="alur">
        <div class="wrap">
            <span class="label">Alur kerja</span>
            <h2>Empat langkah dari perjanjian ke laporan</h2>
            <p class="pengantar">Satu alur yang sama dipakai semua prodi, sehingga data fakultas konsisten dan mudah direkap.</p>
            <ol class="alur">
                <li><h3>Catat dokumen</h3><p>Admin fakultas atau prodi mengisi formulir empat langkah dan mengunggah naskah PDF.</p></li>
                <li><h3>Pantau masa berlaku</h3><p>Status dan pengingat otomatis menjaga dokumen tetap berlaku.</p></li>
                <li><h3>Laporkan realisasi</h3><p>Setiap kegiatan dicatat bersama bukti pelaksanaan, lalu diajukan.</p></li>
                <li><h3>Verifikasi &amp; rekap</h3><p>Admin fakultas memverifikasi; data masuk ke ekspor akreditasi dan rekap pimpinan.</p></li>
            </ol>
        </div>
    </section>

    {{-- PERAN --}}
    <section id="peran" class="gelap">
        <div class="wrap">
            <span class="label">Untuk siapa</span>
            <h2>Empat peran, satu panduan masing-masing</h2>
            <p class="pengantar">Pilih peran Anda untuk melihat panduan bergambar langkah demi langkah.</p>
            <div class="peran">
                <a href="/panduan/pimpinan.html">
                    <span class="lencana hijau" style="align-self:flex-start">Pimpinan</span>
                    <h3>Dekan, wakil dekan, kaprodi</h3>
                    <p>Memantau kondisi kerja sama dan mengunduh rekap untuk pengambilan keputusan.</p>
                    <ul><li>Dasbor &amp; grafik</li><li>Rekap PDF</li><li>Hanya baca</li></ul>
                    <span class="menuju">Buka panduan →</span>
                </a>
                <a href="/panduan/admin-prodi.html">
                    <span class="lencana biru" style="align-self:flex-start">Admin Prodi</span>
                    <h3>Operator / GKM prodi</h3>
                    <p>Mencatat kerja sama dan realisasi kegiatan yang melibatkan program studinya.</p>
                    <ul><li>Buat kerja sama</li><li>Lapor realisasi &amp; bukti</li><li>Ekspor Tridharma</li></ul>
                    <span class="menuju">Buka panduan →</span>
                </a>
                <a href="/panduan/admin-fakultas.html">
                    <span class="lencana kuning" style="align-self:flex-start">Admin Fakultas</span>
                    <h3>Staf urusan kerja sama</h3>
                    <p>Mengelola seluruh data, master mitra dan prodi, serta memverifikasi realisasi.</p>
                    <ul><li>CRUD semua data</li><li>Verifikasi realisasi</li><li>Evaluasi keefektifan</li></ul>
                    <span class="menuju">Buka panduan →</span>
                </a>
                <a href="/panduan/super-admin.html">
                    <span class="lencana merah" style="align-self:flex-start">Super Admin</span>
                    <h3>Pengembang / operator TI</h3>
                    <p>Memegang akses penuh, pengguna dan peran, impor data, dan penjadwalan pengingat.</p>
                    <ul><li>Kelola pengguna &amp; peran</li><li>Impor CSV</li><li>Pemulihan data</li></ul>
                    <span class="menuju">Buka panduan →</span>
                </a>
            </div>
        </div>
    </section>

    {{-- AKREDITASI --}}
    <section id="akreditasi">
        <div class="wrap dua">
            <div>
                <span class="label">Akreditasi &amp; kinerja</span>
                <h2>Data yang biasanya dikumpulkan manual, sudah tersedia</h2>
                <ul class="centang">
                    @foreach ([
                        'Ekspor Excel Tabel Kerja Sama Tridharma per prodi dan tahun TS, tiga lembar: Pendidikan, Penelitian, PkM.',
                        'Simulasi skor elemen Kerja Sama LAMDIK IAPSK 3.0 dari angka realisasi terverifikasi.',
                        'Evaluasi keefektifan kerja sama per tahun, dipakai sebagai indikator (b).',
                        'Rekap PDF untuk pimpinan: status, tingkat, jenis mitra, dan daftar yang akan berakhir.',
                        'Peringatan otomatis untuk kerja sama luar negeri sesuai Permendikbud 14/2014.',
                    ] as $butir)
                        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg><span>{{ $butir }}</span></li>
                    @endforeach
                </ul>
            </div>
            <div class="bingkai">
                <div class="bar"><i></i><i></i><i></i><span>Simulasi Skor LAMDIK</span></div>
                <img src="/panduan/img/landing-lamdik.png" width="1680" height="1000" alt="Halaman simulasi skor elemen Kerja Sama Tridharma LAMDIK IAPSK 3.0.">
            </div>
        </div>
    </section>

    {{-- PENUTUP --}}
    <section class="penutup">
        <div class="wrap">
            <h2>Mulai dari panduan, lanjut ke sistem</h2>
            <p>Belum punya akun? Hubungi admin fakultas atau operator TI untuk dibuatkan akun sesuai peran Anda.</p>
            <div class="aksi">
                <a class="tombol utama" href="/admin">{{ auth()->check() ? 'Buka dasbor' : 'Masuk ke sistem' }} →</a>
                <a class="tombol garis" href="/panduan/">Panduan pengguna</a>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="wrap">
        <span>© {{ date('Y') }} Fakultas Keguruan dan Ilmu Pendidikan, Universitas Siliwangi</span>
        <span>Dasar: Permendikbud No. 14 Tahun 2014 · LAMDIK IAPSK 3.0</span>
    </div>
</footer>

</body>
</html>
