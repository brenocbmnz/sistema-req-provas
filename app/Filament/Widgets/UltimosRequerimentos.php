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
                Tables\Columns\TextColumn::make('nome_completo')
                    ->label('Aluno')
                    ->searchable(),
                Tables\Columns\TextColumn::make('disciplina.nome')
                    ->label('Disciplina'),
                Tables\Columns\TextColumn::make('data_requerimento')
                    ->label('Data')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Reprovado' => 'danger',
                        'Concluído' => 'info',
                        default => 'gray',
                    }),
            ]);
    }
}

