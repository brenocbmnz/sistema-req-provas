<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequerimentoResource\Pages;
use App\Filament\Resources\RequerimentoResource\RelationManagers;
use App\Models\Requerimento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class RequerimentoResource extends Resource
{
    protected static ?string $model = Requerimento::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('aluno_id')
                ->relationship('aluno', 'nome_completo') // Busca Alunos
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('trimestre_id')
                ->relationship('trimestre', 'nome') // Busca Trimestres
                ->required(),
            Forms\Components\Select::make('disciplina_id')
                ->relationship('disciplina', 'nome') // Busca Disciplinas
                ->required(),
            Forms\Components\DatePicker::make('data_requerimento')
                ->default(now())
                ->required(),
            Forms\Components\Textarea::make('motivo')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Select::make('status')
                ->options([
                    'Pendente' => 'Pendente',
                    'Aprovado' => 'Aprovado',
                    'Reprovado' => 'Reprovado',
                ])
                ->default('Pendente')
                ->required(),
        ]);
}


 public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('aluno.nome_completo')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('disciplina.nome')->searchable(),
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

    protected static ?string $navigationGroup = 'Operacional';
}
