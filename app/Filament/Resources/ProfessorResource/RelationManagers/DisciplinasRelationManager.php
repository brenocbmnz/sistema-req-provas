<?php

namespace App\Filament\Resources\ProfessorResource\RelationManagers;

use App\Enums\NivelEnsino;
use App\Models\Disciplina;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DisciplinasRelationManager extends RelationManager
{
    protected static string $relationship = 'disciplinas';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Disciplina')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nivel_ensino')
                    ->label('Nível de Ensino')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('nivel_ensino')
                    ->label('Nível de Ensino')
                    ->options(collect(NivelEnsino::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])->toArray()),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->orderBy('nome'))
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
