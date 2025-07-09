<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MotivoRequerimento;
use App\Enums\NivelEnsino;

class Requerimento extends Model
{
    use HasFactory;

    protected $fillable = [
        'aluno_id',      // Campo opcional
        'nome_completo', // Dados do aluno
        'nivel_ensino',  // Dados do aluno
        'ano',           // Dados do aluno
        'turma',         // Dados do aluno
        'trimestre_id',
        'disciplina_id',
        'data_requerimento',
        'motivo',
        'observacao',
        'status',
        'professor_id',
    ];

    // Apenas cast de data, sem enums para evitar problemas com Filament
    protected $casts = [
        'data_requerimento' => 'date',
    ];

    // Define os valores padrão para novos registros
    protected $attributes = [
        'status' => 'Aprovado',
    ];

    // Define as relações com os outros models
    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function trimestre(): BelongsTo
    {
        return $this->belongsTo(Trimestre::class);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    public function professor()
    {
        return $this->belongsTo(Professor::class);
    }
}
