<?php

namespace App\Filament\Resources\DisciplinaResource\Pages;

use App\Filament\Resources\DisciplinaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Pages\Base\BaseEditPage;
use Filament\Notifications\Notification;

class EditDisciplina extends BaseEditPage
{
    protected static string $resource = DisciplinaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record, Actions\DeleteAction $action) {
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
        ];
    }
}
