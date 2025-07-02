<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\MotivoRequerimento;
class Requerimento extends Model
{
    use HasFactory;


    protected $fillable = [
        'aluno_id',
        'trimestre_id',
        'disciplina_id',
        'data_requerimento',
        'motivo',
        'observacao', // Adicione o novo campo
        'status',
        'professor_id',
    ];

    // Converte os campos para os Enums correspondentes
    protected $casts = [
        'motivo' => MotivoRequerimento::class,
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
