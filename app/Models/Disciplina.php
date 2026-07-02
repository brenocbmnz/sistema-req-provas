<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disciplina extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'nivel_ensino',
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
