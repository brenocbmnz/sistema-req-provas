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

    /**
     * Scope para requerimentos que devem ser marcados como concluídos
     */
    public function scopeParaConcluir($query)
    {
        return $query->where('status', 'Aprovado')
            ->whereHas('trimestre', function ($query) {
                $query->where('data_fim', '<', now()->toDateString());
            });
    }

    /**
     * Verifica se o requerimento deve ser marcado como concluído
     */
    public function deveSerConcluido(): bool
    {
        return $this->status === 'Aprovado' && 
               $this->trimestre && 
               $this->trimestre->data_fim < now()->toDateString();
    }

    /**
     * Retorna estatísticas dos requerimentos por status
     */
    public static function estatisticasPorStatus(): array
    {
        return static::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
     * Conta quantos requerimentos podem ser marcados como concluídos
     */
    public static function contarParaConcluir(): int
    {
        return static::paraConcluir()->count();
    }
}
