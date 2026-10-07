<button type="button" class="sakelar-tema" data-sakelar-tema aria-label="Ganti tema terang/gelap" title="Ganti tema terang/gelap">
    <svg class="ikon-bulan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
    <svg class="ikon-matahari" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
</button>
@once
    <script>
        document.addEventListener('click', function (e) {
            if (! e.target.closest('[data-sakelar-tema]')) return;
            var gelap = ! document.documentElement.classList.contains('dark');
            try { localStorage.setItem('theme', gelap ? 'dark' : 'light'); } catch (err) {}
            document.documentElement.classList.toggle('dark', gelap);
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: gelap ? 'dark' : 'light' }));
        });
    </script>
@endonce
