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
            Stat::make('Total de Requerimentos', Requerimento::count()),
            Stat::make('Requerimentos Pendentes', Requerimento::where('status', 'Pendente')->count()),
            Stat::make('Requerimentos Aprovados', Requerimento::where('status', 'Aprovado')->count()),
        ];
    }
}

