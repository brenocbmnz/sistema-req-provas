<?php

use App\Models\Requerimento;

Requerimento::with('disciplina')->orderBy('id')->get()->each(function ($r) {
    echo $r->id
        . ' | data=' . $r->data_requerimento
        . ' | req_nivel=' . var_export($r->nivel_ensino, true)
        . ' | disc_id=' . var_export($r->disciplina_id, true)
        . ' | disc_nivel=' . var_export(optional($r->disciplina)->nivel_ensino, true)
        . PHP_EOL;
});
