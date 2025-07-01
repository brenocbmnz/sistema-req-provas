<?php

namespace App\Models;

use App\Enums\NivelEnsino; // Importe o Enum
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_completo',
        'nivel_ensino',
        'ano',
    ];

    // Converte a string do banco para o objeto Enum
    protected $casts = [
        'nivel_ensino' => NivelEnsino::class,
    ];
}