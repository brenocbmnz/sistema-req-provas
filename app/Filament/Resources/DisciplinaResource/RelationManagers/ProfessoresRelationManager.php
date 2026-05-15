<?php

namespace App\Filament\Resources\DisciplinaResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Professor;

class ProfessoresRelationManager extends RelationManager
{
    protected static string $relationship = 'professores';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                Tables\Columns\TextColumn::make('nome'),
                Tables\Columns\TextColumn::make('disciplinas_count')
                    ->label('Disciplinas')
                    ->counts('disciplinas'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Botão para associar um professor que já existe
                Tables\Actions\AttachAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('nome'))
                    ->preloadRecordSelect(),
            ])
            ->actions([
                // Botão para desassociar o professor desta disciplina
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
