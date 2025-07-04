<?php

namespace App\Filament\Resources\TrimestreResource\Pages;

use App\Filament\Resources\TrimestreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Pages\Base\BaseEditPage;
use Filament\Notifications\Notification;

class EditTrimestre extends BaseEditPage
{
    protected static string $resource = TrimestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record, Actions\DeleteAction $action) {
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
        ];
    }
}
