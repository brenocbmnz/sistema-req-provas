<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Requerimento;
use Carbon\Carbon;

class AtualizarStatusRequerimentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requerimentos:atualizar-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza automaticamente o status dos requerimentos para "Concluído" quando a data final do trimestre passar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoje = Carbon::today();
        
        // Busca requerimentos que devem ser marcados como concluídos
        $requerimentos = Requerimento::with('trimestre')
            ->paraConcluir()
            ->get();

        $count = $requerimentos->count();
        
        if ($count === 0) {
            $this->info('Nenhum requerimento precisa ser atualizado.');
            return 0;
        }

        // Atualiza os status para "Concluído"
        $atualizado = 0;
        foreach ($requerimentos as $requerimento) {
            $requerimento->update(['status' => 'Concluído']);
            $atualizado++;
            
            $this->line("✓ Requerimento ID {$requerimento->id} de {$requerimento->nome_completo} atualizado para 'Concluído'");
            $this->line("  Trimestre: {$requerimento->trimestre->nome} (terminou em {$requerimento->trimestre->data_fim->format('d/m/Y')})");
        }

        $this->info("✅ {$atualizado} requerimento(s) atualizado(s) para status 'Concluído'.");
        
        return 0;
    }
}
