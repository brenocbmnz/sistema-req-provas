<?php

namespace App\Filament\Resources\ProfessorResource\Pages;

use App\Filament\Resources\ProfessorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Pages\Base\BaseEditPage;


class EditProfessor extends BaseEditPage
{
    protected static string $resource = ProfessorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
