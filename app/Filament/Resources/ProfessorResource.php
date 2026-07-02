<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfessorResource\Pages;
use App\Models\Professor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Infolists;
use Filament\Notifications\Notification;

class ProfessorResource extends Resource
{
    protected static ?string $model = Professor::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationGroup = 'Cadastros Escolares';
    
    protected static ?string $navigationLabel = 'Professores';
    
    
    protected static ?string $pluralModelLabel = 'Professores';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('disciplinas')
                    ->label('Disciplinas')
                    ->multiple()
                    ->relationship('disciplinas', 'nome')
                    ->preload()
                    ->getOptionLabelFromRecordUsing(
                        fn ($record) => $record->nome . ($record->nivel_ensino ? ' — ' . $record->nivel_ensino->value : '')
                    )
                    ->placeholder('Selecione as disciplinas')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('disciplinas_count')
                    ->label('Disciplinas')
                    ->counts('disciplinas')
                    ->sortable(),
                Tables\Columns\TextColumn::make('disciplinas.nivel_ensino')
                    ->label('Nível de Ensino')
                    ->badge()
                    ->color('primary')
                    ->separator(', ')
                    ->state(fn ($record) => $record->disciplinas->pluck('nivel_ensino')
                        ->filter()
                        ->unique()
                        ->map(fn ($n) => $n->value)
                        ->values()
                        ->toArray()
                    )
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordAction('visualizar')
            ->recordUrl(null)
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('visualizar')
                        ->label('Visualizar')
                        ->icon('heroicon-o-eye')
                        ->modalHeading(fn ($record) => 'Professor: ' . $record->nome)
                        ->modalDescription('Informações detalhadas do professor')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->extraModalFooterActions([
                            Action::make('editar')
                                ->label('Editar')
                                ->icon('heroicon-o-pencil-square')
                                ->url(fn ($record) => static::getUrl('edit', ['record' => $record]))
                        ])
                        ->infolist([
                            \Filament\Infolists\Components\Section::make('Informações do Professor')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('nome')
                                        ->label('Nome do Professor'),
                                    \Filament\Infolists\Components\TextEntry::make('disciplinas_count')
                                        ->label('Número de Disciplinas')
                                        ->state(fn ($record) => $record->disciplinas()->count()),
                                    \Filament\Infolists\Components\TextEntry::make('created_at')
                                        ->label('Criado em')
                                        ->date('d/m/Y H:i'),
                                ])->columns(2),
                            \Filament\Infolists\Components\Section::make('Disciplinas Lecionadas')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('disciplinas')
                                        ->label('')
                                        ->listWithLineBreaks()
                                        ->bulleted()
                                        ->state(fn ($record) => $record->disciplinas->map(function ($d) {
                                            return $d->nome . ($d->nivel_ensino ? ' (' . $d->nivel_ensino->value . ')' : '');
                                        })->toArray() ?: ['Nenhuma disciplina associada'])
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record, Tables\Actions\DeleteAction $action) {
                            // Verifica se o professor tem requerimentos associados
                            if ($record->requerimentos()->exists()) {
                                Notification::make()
                                    ->title('Não é possível excluir!')
                                    ->body('Este professor não pode ser excluído pois possui requerimentos associados.')
                                    ->danger()
                                    ->send();
                                
                                $action->cancel();
                            }
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records, Tables\Actions\DeleteBulkAction $action) {
                            // Verifica se algum professor tem requerimentos associados
                            foreach ($records as $record) {
                                if ($record->requerimentos()->exists()) {
                                    Notification::make()
                                        ->title('Não é possível excluir!')
                                        ->body('Um ou mais professores selecionados possuem requerimentos associados e não podem ser excluídos.')
                                        ->danger()
                                        ->send();
                                    
                                    $action->cancel();
                                    return;
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfessors::route('/'),
            'create' => Pages\CreateProfessor::route('/create'),
            'edit' => Pages\EditProfessor::route('/{record}/edit'),
        ];
    }
}
