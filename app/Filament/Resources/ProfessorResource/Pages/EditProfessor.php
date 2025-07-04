<?php

namespace App\Filament\Resources\ProfessorResource\Pages;

use App\Filament\Resources\ProfessorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Pages\Base\BaseEditPage;
use Filament\Notifications\Notification;


class EditProfessor extends BaseEditPage
{
    protected static string $resource = ProfessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record, Actions\DeleteAction $action) {
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
        ];
    }
}
