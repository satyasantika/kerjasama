<?php

namespace App\Filament\Widgets;

use App\Enums\JenisMitra;
use App\Models\KerjaSama;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class KerjaSamaPerJenisMitraChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Kerja sama berlaku per jenis mitra';

    protected ?string $description = 'Kerja sama dengan beberapa mitra dihitung pada tiap jenis mitranya.';

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return auth()->user()?->mengurusKerjaSama() ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $jumlah = KerjaSama::berlaku()
            ->join('kerja_sama_mitra', 'kerja_sama_mitra.kerja_sama_id', '=', 'kerja_sama.id')
            ->join('mitra', 'mitra.id', '=', 'kerja_sama_mitra.mitra_id')
            ->select('mitra.jenis', DB::raw('count(distinct kerja_sama.id) as total'))
            ->groupBy('mitra.jenis')
            ->pluck('total', 'jenis');

        return [
            'datasets' => [[
                'label' => 'Kerja sama',
                'data' => collect(JenisMitra::cases())->map(fn (JenisMitra $j) => (int) ($jumlah[$j->value] ?? 0))->all(),
            ]],
            'labels' => collect(JenisMitra::cases())->map->label()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['ticks' => ['precision' => 0]]]];
    }
}
