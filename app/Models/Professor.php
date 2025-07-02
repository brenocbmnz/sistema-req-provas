<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professor extends Model
{
    /** @use HasFactory<\Database\Factories\ProfessorFactory> */
    use HasFactory;

    protected $fillable = ['nome'];

public function disciplinas()
{
    return $this->belongsToMany(Disciplina::class, 'disciplina_professor');
}

}


