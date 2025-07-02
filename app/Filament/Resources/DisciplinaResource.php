<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisciplinaResource\Pages;
// Adicione a importação do novo Relation Manager
use App\Filament\Resources\DisciplinaResource\RelationManagers\ProfessoresRelationManager; 
use App\Models\Disciplina;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DisciplinaResource extends Resource
{
    protected static ?string $model = Disciplina::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'Cadastros Escolares';
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nome')
                ->required()
                ->unique(ignoreRecord: true) 
                ->columnSpanFull(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable(),
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
