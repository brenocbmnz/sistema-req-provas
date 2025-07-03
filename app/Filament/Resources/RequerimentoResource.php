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
use Illuminate\Support\Collection;

class RequerimentoResource extends Resource
{
    protected static ?string $model = Requerimento::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                            ->options([
                                NivelEnsino::FUNDAMENTAL1->value => NivelEnsino::FUNDAMENTAL1->value,
                                NivelEnsino::FUNDAMENTAL2->value => NivelEnsino::FUNDAMENTAL2->value,
                                NivelEnsino::MEDIO->value => NivelEnsino::MEDIO->value,
                            ])
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
                            ->options([
                                MotivoRequerimento::ATESTADO->value => MotivoRequerimento::ATESTADO->value,
                                MotivoRequerimento::JOGOS->value => MotivoRequerimento::JOGOS->value,
                                MotivoRequerimento::VIAGEM->value => MotivoRequerimento::VIAGEM->value,
                                MotivoRequerimento::OUTROS->value => MotivoRequerimento::OUTROS->value,
                            ])
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
