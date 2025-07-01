<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requerimento extends Model
{
    use HasFactory;

    protected $fillable = [
        'aluno_id',
        'trimestre_id',
        'disciplina_id',
        'data_requerimento',
        'motivo',
        'status',
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
}
