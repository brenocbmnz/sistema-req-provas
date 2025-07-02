<?php

namespace App\Filament\Pages\Base;

use Filament\Resources\Pages\CreateRecord;

abstract class BaseCreatePage extends CreateRecord
{
    // A função de redirecionamento fica aqui, em um só lugar!
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}