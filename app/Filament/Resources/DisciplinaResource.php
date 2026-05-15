<?php

namespace App\Filament\Resources;

use App\Enums\NivelEnsino;
use App\Filament\Resources\DisciplinaResource\Pages;
use App\Filament\Resources\DisciplinaResource\RelationManagers\ProfessoresRelationManager;
use App\Models\Disciplina;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Infolists;
use Filament\Notifications\Notification;

class DisciplinaResource extends Resource
{
    protected static ?string $model = Disciplina::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'Cadastros Escolares';
    protected static ?int $navigationSort = 2;
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nome')
                ->required()
                ->unique(ignoreRecord: true)
                ->columnSpanFull(),
            Forms\Components\Select::make('nivel_ensino')
                ->label('Nível de Ensino')
                ->options(collect(NivelEnsino::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value])->toArray())
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
                Tables\Columns\TextColumn::make('nivel_ensino')
                    ->label('Nível de Ensino')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('professores_count')
                    ->label('Professores')
                    ->counts('professores')
                    ->sortable(),
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
                        ->modalHeading(fn ($record) => 'Disciplina: ' . $record->nome)
                        ->modalDescription('Informações detalhadas da disciplina')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->extraModalFooterActions([
                            Action::make('editar')
                                ->label('Editar')
                                ->icon('heroicon-o-pencil-square')
                                ->url(fn ($record) => static::getUrl('edit', ['record' => $record]))
                        ])
                        ->infolist([
                            \Filament\Infolists\Components\Section::make('Informações da Disciplina')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('nome')
                                        ->label('Nome da Disciplina'),
                                    \Filament\Infolists\Components\TextEntry::make('nivel_ensino')
                                        ->label('Nível de Ensino')
                                        ->badge()
                                        ->color('primary'),
                                    \Filament\Infolists\Components\TextEntry::make('professores_count')
                                        ->label('Número de Professores')
                                        ->state(fn ($record) => $record->professores()->count()),
                                    \Filament\Infolists\Components\TextEntry::make('created_at')
                                        ->label('Criado em')
                                        ->date('d/m/Y H:i'),
                                ])->columns(2),
                            \Filament\Infolists\Components\Section::make('Professores Associados')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('professores')
                                        ->label('')
                                        ->listWithLineBreaks()
                                        ->bulleted()
                                        ->state(fn ($record) => $record->professores->pluck('nome')->toArray() ?: ['Nenhum professor associado'])
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record, Tables\Actions\DeleteAction $action) {
                            // Verifica se a disciplina tem requerimentos associados
                            if ($record->requerimentos()->exists()) {
                                Notification::make()
                                    ->title('Não é possível excluir!')
                                    ->body('Esta disciplina não pode ser excluída pois possui requerimentos associados.')
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
                            // Verifica se alguma disciplina tem requerimentos associados
                            foreach ($records as $record) {
                                if ($record->requerimentos()->exists()) {
                                    Notification::make()
                                        ->title('Não é possível excluir!')
                                        ->body('Uma ou mais disciplinas selecionadas possuem requerimentos associados e não podem ser excluídas.')
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
        // Registramos o nosso novo gerenciador aqui
        return [
            ProfessoresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDisciplinas::route('/'),
            'create' => Pages\CreateDisciplina::route('/create'),
            'edit' => Pages\EditDisciplina::route('/{record}/edit'),
        ];
    }
}
