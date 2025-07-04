<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trimestre extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'data_inicio',
        'data_fim',
    ];

    public function requerimentos()
    {
        return $this->hasMany(Requerimento::class);
    }
}
