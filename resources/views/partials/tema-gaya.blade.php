{{-- Token desain bersama (landing, login, halaman eror) + gaya sakelar tema. --}}
<style>
    :root {
        color-scheme: light;
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
    :root.dark {
        color-scheme: dark;
        --kertas: #17140f;
        --kertas-2: #211c14;
        --tinta: #f5efe3;
        --tinta-2: #b8ae9c;
        --garis: #3a3224;
        --amber-muda: #3a2a08;
        --amber-tua: #fbbf24;
    }
    .sakelar-tema { display: inline-grid; place-items: center; width: 38px; height: 38px; padding: 0; border-radius: 10px; border: 1.5px solid var(--garis); background: transparent; color: var(--tinta); cursor: pointer; transition: background .15s, border-color .15s; }
    .sakelar-tema:hover { background: var(--kertas-2); border-color: var(--amber); }
    .sakelar-tema:focus-visible { outline: 2px solid var(--amber); outline-offset: 2px; }
    .sakelar-tema svg { width: 19px; height: 19px; }
    .sakelar-tema .ikon-matahari { display: none; }
    :root.dark .sakelar-tema .ikon-matahari { display: block; }
    :root.dark .sakelar-tema .ikon-bulan { display: none; }
</style>
