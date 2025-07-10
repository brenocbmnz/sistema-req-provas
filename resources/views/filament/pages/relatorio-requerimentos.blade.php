<x-filament-panels::page>
    <!-- Seção Superior - Relatórios Gerais com Filtro de Data -->
    <div class="mb-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Relatórios Gerais por Período
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Selecione o período e a forma de ordenação para gerar os relatórios gerais de requerimentos.
                Você pode organizar os dados por <strong>Nível de Ensino</strong>, <strong>Turma</strong>,
                <strong>Disciplina</strong>, <strong>Professor</strong>, <strong>Nome do Aluno</strong> ou
                <strong>Data</strong>.
            </p>

            <form wire:submit.prevent="generateRelatoriosGerais">
                {{ $this->formGeral }}

                <div class="mt-6 flex gap-4 flex-wrap">
                    <x-filament::button type="button" wire:click="generateRelatorioPorSerie" color="primary">
                        Gerar Relatório Geral
                    </x-filament::button>

                    <x-filament::button type="button" wire:click="generateRelatorioCompleto" color="primary">
                        Gerar Relatório Completo
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>

    <!-- Seção Inferior - Relatório Personalizado com Filtros -->
    <div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        Relatório Personalizado
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Configure filtros básicos abaixo. Use o botão "Filtros Avançados" para adicionar mais opções de
                        filtro.
                    </p>
                </div>
                <div class="flex gap-2">
                    {{ ($this->filtrosAction)(['size' => 'sm']) }}
                </div>
            </div>

            <form wire:submit.prevent="generateReport">
                {{ $this->form }}

                <!-- Resumo dos Filtros Avançados Ativos -->
                @php
                    $data = $this->form->getState();
                    $filtrosAtivos = [];
                    $filtrosDisponiveis = [];
                    $sessaoFiltros = $this->getFiltrosAtivos();

                    // Verifica quais filtros estão habilitados (marcados no modal)
                    if (!empty($sessaoFiltros['filtrar_nivel_ensino'])) {
                        $filtrosDisponiveis[] = 'Nível de Ensino';
                        if (!empty($data['nivel_ensino'])) {
                            $filtrosAtivos[] = 'Nível de Ensino: ' . implode(', ', (array) $data['nivel_ensino']);
                        }
                    }
                    if (!empty($sessaoFiltros['filtrar_ano'])) {
                        $filtrosDisponiveis[] = 'Ano/Série';
                        if (!empty($data['ano'])) {
                            $filtrosAtivos[] = 'Ano/Série: ' . implode(', ', (array) $data['ano']);
                        }
                    }
                    if (!empty($sessaoFiltros['filtrar_turma'])) {
                        $filtrosDisponiveis[] = 'Turma';
                        if (!empty($data['turma'])) {
                            $filtrosAtivos[] = 'Turma: ' . implode(', ', (array) $data['turma']);
                        }
                    }
                    if (!empty($sessaoFiltros['filtrar_disciplina'])) {
                        $filtrosDisponiveis[] = 'Disciplina';
                        if (!empty($data['disciplina_id'])) {
                            $disciplinas = \App\Models\Disciplina::whereIn('id', (array) $data['disciplina_id'])->pluck('nome')->toArray();
                            $filtrosAtivos[] = 'Disciplina: ' . implode(', ', $disciplinas);
                        }
                    }
                    if (!empty($sessaoFiltros['filtrar_professor'])) {
                        $filtrosDisponiveis[] = 'Professor';
                        if (!empty($data['professor_id'])) {
                            $professores = \App\Models\Professor::whereIn('id', (array) $data['professor_id'])->pluck('nome')->toArray();
                            $filtrosAtivos[] = 'Professor: ' . implode(', ', $professores);
                        }
                    }
                @endphp

                <div class="mt-6">
                    <x-filament::button type="submit">
                        Gerar Relatório Personalizado
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>