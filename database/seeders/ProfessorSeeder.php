<?php

namespace Database\Seeders;

use App\Models\Professor;
use App\Models\Disciplina;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar alguns professores de exemplo
        $professores = [
            'Prof. Maria Silva',
            'Prof. João Santos',
            'Prof. Ana Oliveira',
            'Prof. Carlos Ferreira',
            'Prof. Lucia Costa',
            'Prof. Roberto Lima',
            'Prof. Patricia Souza',
            'Prof. Marcos Almeida',
        ];

        foreach ($professores as $nome) {
            Professor::firstOrCreate(['nome' => $nome]);
        }

        // Associar professores a disciplinas aleatoriamente
        $professores = Professor::all();
        $disciplinas = Disciplina::all();

        foreach ($professores as $professor) {
            // Cada professor ensina de 1 a 3 disciplinas
            $disciplinasAleatorias = $disciplinas->random(rand(1, 3));
            $professor->disciplinas()->syncWithoutDetaching($disciplinasAleatorias->pluck('id'));
        }
    }
}
