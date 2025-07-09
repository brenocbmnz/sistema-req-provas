<?php

namespace App\Filament\Widgets;

use App\Models\Requerimento;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RequerimentosStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total de Requerimentos', Requerimento::count())
                ->color('gray'),
            Stat::make('Requerimentos Aprovados', Requerimento::where('status', 'Aprovado')->count())
                ->color('success'),
            Stat::make('Requerimentos Concluídos', Requerimento::where('status', 'Concluído')->count())
                ->color('info'),
        ];
    }
}

