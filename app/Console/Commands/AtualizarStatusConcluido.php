<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Requerimento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AtualizarStatusConcluido extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requerimentos:atualizar-concluidos
                            {--dry-run : Mostra quantos requerimentos seriam atualizados sem fazer mudanças}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza automaticamente o status de requerimentos aprovados para "Concluído" quando o trimestre termina';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando requerimentos que devem ser marcados como concluídos...');

        // Busca requerimentos aprovados cujo trimestre já terminou
        $requerimentosParaConcluir = Requerimento::where('status', 'Aprovado')
            ->whereHas('trimestre', function ($query) {
                $query->where('data_fim', '<', Carbon::now()->toDateString());
            })
            ->with(['trimestre', 'disciplina', 'professor']);

        $total = $requerimentosParaConcluir->count();

        if ($total === 0) {
            $this->info('✅ Nenhum requerimento encontrado para atualizar.');
            return 0;
        }

        $this->info("📋 Encontrados {$total} requerimento(s) para atualizar:");

        // Mostra detalhes dos requerimentos
        $requerimentos = $requerimentosParaConcluir->get();
        
        $this->table(
            ['ID', 'Aluno', 'Disciplina', 'Professor', 'Trimestre', 'Data Fim'],
            $requerimentos->map(function ($req) {
                return [
                    $req->id,
                    $req->nome_completo,
                    $req->disciplina->nome ?? 'N/A',
                    $req->professor->nome ?? 'N/A',
                    $req->trimestre->nome ?? 'N/A',
                    $req->trimestre->data_fim ?? 'N/A'
                ];
            })->toArray()
        );

        if ($this->option('dry-run')) {
            $this->warn('🔍 Execução em modo dry-run - nenhuma alteração foi feita.');
            return 0;
        }

        if ($this->confirm("Deseja atualizar {$total} requerimento(s) para status 'Concluído'?", true)) {
            $atualizados = $requerimentosParaConcluir->update(['status' => 'Concluído']);
            
            $this->info("✅ {$atualizados} requerimento(s) atualizado(s) com sucesso!");
            
            // Log da operação
            Log::info("Status de requerimentos atualizados automaticamente", [
                'total_atualizados' => $atualizados,
                'executado_em' => now(),
                'comando' => 'requerimentos:atualizar-concluidos'
            ]);
        } else {
            $this->info('❌ Operação cancelada pelo usuário.');
        }

        return 0;
    }
}
