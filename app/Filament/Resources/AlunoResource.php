<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlunoResource\Pages;
use App\Filament\Resources\AlunoResource\RelationManagers;
use App\Models\Aluno;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Enums\NivelEnsino;

class AlunoResource extends Resource
{
    protected static ?string $model = Aluno::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nome_completo')->required(),
            Forms\Components\Select::make('nivel_ensino')
                ->options(NivelEnsino::class) // Filament já entende Enums!
                ->required(),
            Forms\Components\TextInput::make('ano')
                ->numeric()
                ->required(),
        ]);
}

// Dentro do método table()
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nome_completo')->searchable(),
            Tables\Columns\TextColumn::make('nivel_ensino')->badge(),
            Tables\Columns\TextColumn::make('ano'),
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
            'index' => Pages\ListAlunos::route('/'),
            'create' => Pages\CreateAluno::route('/create'),
            'edit' => Pages\EditAluno::route('/{record}/edit'),
        ];
    }
    protected static ?string $navigationGroup = 'Cadastros Escolares';
}
