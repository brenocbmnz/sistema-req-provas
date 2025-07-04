<?php

namespace App\Filament\Resources\ProfessorResource\Pages;

use App\Filament\Resources\ProfessorResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Pages\Base\BaseCreatePage;


class CreateProfessor extends BaseCreatePage
{
    protected static string $resource = ProfessorResource::class;
}
