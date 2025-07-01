<?php

namespace App\Filament\Resources\RequerimentoResource\Pages;

use App\Filament\Resources\RequerimentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequerimento extends EditRecord
{
    protected static string $resource = RequerimentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
