<?php

namespace App\Filament\Resources\TrimestreResource\Pages;

use App\Filament\Resources\TrimestreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Pages\Base\BaseEditPage;

class EditTrimestre extends BaseEditPage
{
    protected static string $resource = TrimestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
