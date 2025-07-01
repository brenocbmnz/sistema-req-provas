<?php
namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Requerimento;
use Filament\Widgets\TableWidget as BaseWidget;

class UltimosRequerimentos extends BaseWidget
{
    protected static ?int $sort = 2; // Ordem no dashboard
    protected int | string | array $columnSpan = 'full'; // Ocupar toda a largura

    public function table(Table $table): Table
    {
        return $table
            ->query(Requerimento::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('aluno.nome_completo'),
                Tables\Columns\TextColumn::make('disciplina.nome'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Reprovado' => 'danger',
                        default => 'gray',
                    }),
            ]);
    }
}

