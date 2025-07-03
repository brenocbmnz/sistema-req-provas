<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequerimentoResource\Pages;
use App\Enums\MotivoRequerimento;
use App\Enums\NivelEnsino;
use App\Models\Professor;
use App\Models\Requerimento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class RequerimentoResource extends Resource
{
    protected static ?string $model = Requerimento::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Operacional';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Seção para os dados do Aluno
                Forms\Components\Section::make('Dados do Aluno')
                    ->schema([
                        Forms\Components\TextInput::make('nome_completo')
                            ->label('Nome Completo do Aluno')
                            ->required(),

                        Forms\Components\Select::make('nivel_ensino')
                            ->label('Nível de Ensino')
                            ->options(NivelEnsino::class)
                            ->live()
                            ->required(),

                        Forms\Components\Select::make('ano')
                            ->label('Ano/Série')
                            ->options(fn (Get $get): array => match ($get('nivel_ensino')) {
                                NivelEnsino::FUNDAMENTAL1->value => array_combine(range(1, 5), range(1, 5)),
                                NivelEnsino::FUNDAMENTAL2->value => array_combine(range(6, 9), range(6, 9)),
                                NivelEnsino::MEDIO->value => array_combine(range(1, 3), range(1, 3)),
                                default => [],
                            })
                            ->required(),
                        
                        Forms\Components\Select::make('turma')
                            ->label('Turma')
                            ->options([
                                'A' => 'A',
                                'B' => 'B',
                                'C' => 'C',
                                'D' => 'D',
                                'E' => 'E',
                            ])
                            ->required(),
                    ])->columns(4),

                // Seção para os dados do Requerimento
                Forms\Components\Section::make('Dados do Requerimento')
                    ->schema([
                        Forms\Components\Select::make('trimestre_id')
                            ->relationship('trimestre', 'nome')
                            ->required(),
                        Forms\Components\Select::make('disciplina_id')
                            ->relationship('disciplina', 'nome')
                            ->live()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(20),
                        Forms\Components\Select::make('professor_id')
                            ->label('Professor')
                            ->options(function (Get $get): Collection {
                                $disciplinaId = $get('disciplina_id');
                                if (!$disciplinaId) {
                                    return collect();
                                }
                                return Professor::query()
                                    ->whereHas('disciplinas', fn ($query) => $query->where('disciplina_id', $disciplinaId))
                                    ->pluck('nome', 'id');
                            })
                            ->required(),
                        Forms\Components\DatePicker::make('data_requerimento')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('motivo')
                            ->options(MotivoRequerimento::class)
                            ->live()
                            ->required(),
                        Forms\Components\Textarea::make('observacao')
                            ->label('Observação / Justificativa')
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => $get('motivo') === MotivoRequerimento::OUTROS->value),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Pendente' => 'Pendente',
                                'Aprovado' => 'Aprovado',
                                'Reprovado' => 'Reprovado',
                            ])
                            ->required()
                            ->visibleOn('edit')
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome_completo')->label('Aluno')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('disciplina.nome')->searchable(),
                Tables\Columns\TextColumn::make('professor.nome')->label('Professor')->searchable(),
                Tables\Columns\TextColumn::make('data_requerimento')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Aprovado' => 'success',
                        'Reprovado' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Pendente' => 'Pendente',
                        'Aprovado' => 'Aprovado',
                        'Reprovado' => 'Reprovado',
                    ]),
                Filter::make('data_requerimento')
                    ->form([
                        Forms\Components\DatePicker::make('criado_em'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['criado_em'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_requerimento', '=', $date),
                            );
                    })
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    // CORREÇÃO AQUI: Usando Action em vez de SelectAction
                    Action::make('alterar_status')
                        ->label('Alterar Status')
                        ->icon('heroicon-o-pencil-square')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Novo Status')
                                ->options([
                                    'Pendente' => 'Pendente',
                                    'Aprovado' => 'Aprovado',
                                    'Reprovado' => 'Reprovado',
                                ])
                                ->default(fn ($record) => $record->status)
                                ->required(),
                        ])
                        ->action(function (array $data, $record): void {
                            $record->update([
                                'status' => $data['status']
                            ]);
                            
                            Notification::make()
                                ->title('Status alterado com sucesso!')
                                ->success()
                                ->send();
                        })
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation()
                    ->modalDescription(new HtmlString('Você tem certeza que gostaria de fazer isso? <div class="text-red-500 dark:text-red-400 text-xl font-semibold">Esta ação irá alterar DELETAR todos requerimentos selecionados.</div>')) ,
                    Tables\Actions\BulkAction::make('alterar_status_bulk')
                        ->label('Alterar Status')
                        ->icon('heroicon-o-pencil-square')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalDescription(new HtmlString('Você tem certeza que gostaria de fazer isso? <div class="text-red-500 dark:text-red-400 text-xl font-semibold">Esta ação irá alterar os status de todos os requerimentos selecionados.</div>'))                        
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Novo Status')
                                ->options([
                                    'Pendente' => 'Pendente',
                                    'Aprovado' => 'Aprovado',
                                    'Reprovado' => 'Reprovado',
                                ])
                                ->required()
                                ->helperText('Este status será aplicado a todos os requerimentos selecionados.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'status' => $data['status']
                                ]);
                            });
                            
                            Notification::make()
                                ->title('Status alterado com sucesso!')
                                ->body('O status de ' . $records->count() . ' requerimento(s) foi alterado para "' . $data['status'] . '".')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequerimentos::route('/'),
            'create' => Pages\CreateRequerimento::route('/create'),
            'edit' => Pages\EditRequerimento::route('/{record}/edit'),
        ];
    }
}