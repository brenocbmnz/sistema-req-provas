<?php

namespace App\Enums;

enum MotivoRequerimento: string
{
    case ATESTADO = 'Atestado Médico';
    case JOGOS = 'Competição Esportiva (Jogos)';
    case OUTROS = 'Outros';
}
