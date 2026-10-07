<?php

namespace App\Filament\Widgets;

use App\Enums\TingkatKerjaSama;
use App\Models\KerjaSama;
use Filament\Widgets\ChartWidget;

class KerjaSamaPerTingkatChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Kerja sama berlaku per tingkat';

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
        $jumlah = KerjaSama::berlaku()->selectRaw('tingkat, count(*) as total')->groupBy('tingkat')->pluck('total', 'tingkat');

        return [
            'datasets' => [[
                'label' => 'Kerja sama',
                'data' => collect(TingkatKerjaSama::cases())->map(fn (TingkatKerjaSama $t) => (int) ($jumlah[$t->value] ?? 0))->all(),
            ]],
            'labels' => collect(TingkatKerjaSama::cases())->map->label()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['ticks' => ['precision' => 0]]]];
    }
}
