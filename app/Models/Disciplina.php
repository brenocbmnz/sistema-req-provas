<?php

namespace App\Models;

use App\Enums\NivelEnsino;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disciplina extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'nivel_ensino',
    ];

    protected $casts = [
        'nivel_ensino' => NivelEnsino::class,
    ];

    public function professores()
{
    return $this->belongsToMany(Professor::class, 'disciplina_professor');
}

public function requerimentos()
{
    return $this->hasMany(Requerimento::class);
}

}
