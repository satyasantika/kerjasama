@php($pengguna = auth()->user())
<div role="status" style="position: sticky; top: 0; z-index: 60; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px 16px; padding: 8px 16px; background: #f59e0b; color: #1c1917; font-size: 14px; font-weight: 500;">
    <span>
        Anda sedang menyamar sebagai <strong>{{ $pengguna?->name }}</strong>
        ({{ $pengguna?->roles->pluck('name')->map(fn ($p) => \App\Enums\Peran::tryFrom($p)?->label() ?? $p)->join(', ') }}).
    </span>
    <form method="POST" action="{{ route('menyamar.akhiri') }}" style="margin: 0;">
        @csrf
        <button type="submit" style="padding: 3px 12px; border-radius: 8px; border: 1.5px solid #1c1917; background: #1c1917; color: #fbf8f2; font-weight: 600; cursor: pointer;">Kembali ke akun saya</button>
    </form>
</div>
