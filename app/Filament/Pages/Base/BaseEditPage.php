<?php

namespace App\Filament\Pages\Base;

use Filament\Resources\Pages\EditRecord;

abstract class BaseEditPage extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}