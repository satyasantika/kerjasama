<x-filament-panels::page>
    {{ $this->form }}

    <x-filament::section heading="Hasil perhitungan">
        @if ($pesan)
            <p class="text-danger-600 dark:text-danger-400">{{ $pesan }}</p>
        @elseif ($hasil)
            <dl class="grid grid-cols-2 gap-4 md:grid-cols-5">
                @foreach (['rk' => 'RK', 'a' => 'A = min(RK, 4)', 'b' => 'B', 'skor_a' => 'Skor (a) = (2A + B) / 3', 'skor' => 'Skor elemen = (3·Skor(a) + Skor(b)) / 4'] as $kunci => $label)
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="text-2xl font-semibold {{ $kunci === 'skor' ? 'text-primary-600 dark:text-primary-400' : '' }}">{{ number_format($hasil[$kunci], 4, ',', '.') }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Catatan: matriks LAMDIK tidak menulis eksplisit kasus NI = 0 dengan 0 &lt; NN &lt; b, dan 0 &lt; NI &lt; a dengan NN = 0.
            Sistem memakai rumus umum untuk keduanya (asumsi implementasi). Rentang TS-2 s.d. TS dan skor (b) dari
            rata-rata evaluasi juga merupakan asumsi yang perlu dikonfirmasi GKM/UPM; hasil ini simulasi, bukan nilai resmi akreditasi.
        </p>
    </x-filament::section>
</x-filament-panels::page>
