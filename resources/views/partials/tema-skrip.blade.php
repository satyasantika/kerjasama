{{-- Terapkan tema tersimpan sebelum render agar tidak berkedip. Kunci 'theme'
     dibagi dengan Filament (light | dark | system), jadi pilihan di landing
     juga berlaku di panel dan sebaliknya. --}}
<script>
    (function () {
        var tema = null;
        try { tema = localStorage.getItem('theme'); } catch (e) {}
        var gelap = tema === 'dark' || ((tema === null || tema === 'system') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', gelap);
    })();
</script>
