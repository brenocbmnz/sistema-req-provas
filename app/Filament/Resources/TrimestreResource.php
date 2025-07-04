<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrimestreResource\Pages;
use App\Filament\Resources\TrimestreResource\RelationManagers;
use App\Models\Trimestre;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class TrimestreResource extends Resource
{
    protected static ?string $model = Trimestre::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

// Dentro do método form()
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nome')->required(),
            Forms\Components\DatePicker::make('data_inicio')->required(),
            Forms\Components\DatePicker::make('data_fim')->required(),
        ]);
}

// Dentro do método table()
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nome')->searchable(),
            Tables\Columns\TextColumn::make('data_inicio')->date('d/m/Y'),
            Tables\Columns\TextColumn::make('data_fim')->date('d/m/Y'),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make()
                ->before(function ($record, Tables\Actions\DeleteAction $action) {
                    // Verifica se o trimestre tem requerimentos associados
                    if ($record->requerimentos()->exists()) {
                        Notification::make()
                            ->title('Não é possível excluir!')
                            ->body('Este trimestre não pode ser excluído pois possui requerimentos associados.')
                            ->danger()
                            ->send();
                        
                        $action->cancel();
                    }
                }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($records, Tables\Actions\DeleteBulkAction $action) {
                        // Verifica se algum trimestre tem requerimentos associados
                        foreach ($records as $record) {
                            if ($record->requerimentos()->exists()) {
                                Notification::make()
                                    ->title('Não é possível excluir!')
                                    ->body('Um ou mais trimestres selecionados possuem requerimentos associados e não podem ser excluídos.')
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrimestres::route('/'),
            'create' => Pages\CreateTrimestre::route('/create'),
            'edit' => Pages\EditTrimestre::route('/{record}/edit'),
        ];
    }
    protected static ?string $navigationGroup = 'Cadastros Escolares';

}
