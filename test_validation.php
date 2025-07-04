<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->boot();

echo "=== Teste de Validação de Exclusão ===\n";

try {
    // Verificar se há professores
    $professoresCount = App\Models\Professor::count();
    echo "Professores no banco: $professoresCount\n";
    
    if ($professoresCount === 0) {
        // Criar um professor
        $professor = App\Models\Professor::create(['nome' => 'Professor Teste']);
        echo "Professor criado: {$professor->nome}\n";
    } else {
        $professor = App\Models\Professor::first();
        echo "Professor encontrado: {$professor->nome}\n";
    }
    
    // Criar disciplina
    $disciplina = App\Models\Disciplina::create(['nome' => 'Matemática Teste ' . time()]);
    echo "Disciplina criada: {$disciplina->nome}\n";
    
    // Criar trimestre
    $trimestre = App\Models\Trimestre::create([
        'nome' => '1º Trimestre 2025 ' . time(),
        'data_inicio' => '2025-01-01',
        'data_fim' => '2025-04-30'
    ]);
    echo "Trimestre criado: {$trimestre->nome}\n";
    
    // Criar requerimento
    $requerimento = App\Models\Requerimento::create([
        'nome_completo' => 'João da Silva Teste',
        'nivel_ensino' => 'medio',
        'ano' => 1,
        'turma' => 'A',
        'trimestre_id' => $trimestre->id,
        'disciplina_id' => $disciplina->id,
        'professor_id' => $professor->id,
        'data_requerimento' => now(),
        'motivo' => 'falta_prova',
        'status' => 'Pendente'
    ]);
    echo "Requerimento criado para: {$requerimento->nome_completo}\n";
    
    // Testar se a disciplina tem requerimentos
    $hasRequerimentos = $disciplina->requerimentos()->exists();
    echo "Disciplina tem requerimentos: " . ($hasRequerimentos ? 'SIM' : 'NÃO') . "\n";
    
    // Testar se o professor tem requerimentos
    $hasRequerimentos = $professor->requerimentos()->exists();
    echo "Professor tem requerimentos: " . ($hasRequerimentos ? 'SIM' : 'NÃO') . "\n";
    
    // Testar se o trimestre tem requerimentos
    $hasRequerimentos = $trimestre->requerimentos()->exists();
    echo "Trimestre tem requerimentos: " . ($hasRequerimentos ? 'SIM' : 'NÃO') . "\n";
    
    echo "\n=== Teste concluído com sucesso! ===\n";
    echo "Agora você pode testar a exclusão no painel admin.\n";
    echo "As disciplinas, professores e trimestres com requerimentos NÃO devem poder ser excluídos.\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
