<?php

namespace App\Filament\Resources\RequerimentoResource\Pages;

use App\Filament\Resources\RequerimentoResource;
use App\Models\Aluno;
use Filament\Resources\Pages\CreateRecord;

class CreateRequerimento extends CreateRecord
{
    protected static string $resource = RequerimentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Pega os dados do aluno do formulário e cria um novo registro
        $aluno = Aluno::create([
            'nome_completo' => $data['nome_completo'],
            'nivel_ensino' => $data['nivel_ensino'],
            'ano' => $data['ano'],
        ]);

        // 2. Adiciona o ID do aluno recém-criado aos dados do requerimento
        $data['aluno_id'] = $aluno->id;

        // 3. Remove os dados temporários do aluno para não tentar salvá-los na tabela de requerimentos
        unset($data['nome_completo'], $data['nivel_ensino'], $data['ano']);

        // 4. Retorna os dados modificados para que o Filament possa criar o requerimento
        return $data;
    }
}
