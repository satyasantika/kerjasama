@include('partials.tema-gaya')
<link rel="stylesheet" href="{{ url('/fonts/filament/filament/inter/index.css') }}">
<style>
    /* Selaraskan halaman login dengan landing page (token dari partials.tema-gaya) */
    .fi-simple-layout { background: var(--kertas); color: var(--tinta); font-family: var(--sans); position: relative; min-height: 100vh; }
    .fi-simple-layout::before { content: ""; position: fixed; inset: 0; background-image: linear-gradient(var(--garis) 1px, transparent 1px), linear-gradient(90deg, var(--garis) 1px, transparent 1px); background-size: 48px 48px; opacity: .45; mask-image: radial-gradient(ellipse 60% 55% at 50% 40%, #000, transparent 75%); pointer-events: none; }
    .fi-simple-main-ctn { position: relative; }
    .fi-simple-main { background: var(--kertas-2); border: 1px solid var(--garis); border-radius: 16px; box-shadow: 0 30px 60px -30px rgba(120, 80, 10, .35), 0 2px 0 var(--garis); --tw-ring-shadow: 0 0 #0000; }
    .fi-simple-header-heading { font-family: var(--serif); font-weight: 400; letter-spacing: -.015em; color: var(--tinta); }
    .fi-simple-header-subheading { color: var(--tinta-2); }
    .fi-simple-page .fi-logo { font-weight: 800; letter-spacing: -.02em; color: var(--tinta); }
    .fi-simple-main .fi-btn.fi-color-primary { background: var(--amber); color: #1c1917; font-weight: 600; box-shadow: 0 1px 0 #b45309, 0 6px 16px -6px rgba(245, 158, 11, .7); }
    .fi-simple-main .fi-btn.fi-color-primary:hover { background: #fbbf24; }
    .login-atas { position: absolute; top: 0; left: 0; right: 0; z-index: 5; display: flex; align-items: center; justify-content: space-between; padding: 0 clamp(16px, 4vw, 40px); height: 64px; }
    .login-atas .merek { font-weight: 800; letter-spacing: -.02em; font-size: 18px; text-decoration: none; color: var(--tinta); display: flex; align-items: center; gap: 10px; }
    .login-atas .merek i { width: 26px; height: 26px; border-radius: 7px; background: var(--amber); display: grid; place-items: center; }
    .login-atas .merek svg { width: 16px; height: 16px; }
    .login-bawah { text-align: center; margin-top: 20px; font-size: 14px; }
    .login-bawah a { color: var(--tinta-2); text-decoration: none; }
    .login-bawah a:hover { color: var(--tinta); text-decoration: underline; }
</style>
