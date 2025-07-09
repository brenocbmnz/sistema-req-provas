<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Agenda a atualização automática dos status dos requerimentos
Schedule::command('requerimentos:atualizar-status')
    ->daily()
    ->description('Atualiza status dos requerimentos para "Concluído" quando o trimestre terminar');
