<x-filament-panels::page>
    <!-- CSS personalizado para dropdown direcionado -->
    <style>
        /* Estilos para dropdown que abre para cima */
        .dropdown-upward [data-choices] .choices__list--dropdown {
            top: auto !important;
            bottom: 100% !important;
            margin-bottom: 4px !important;
            margin-top: 0 !important;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            border-radius: 6px 6px 6px 6px !important;
        }

        /* Ajuste para o tema escuro */
        .dark .dropdown-upward [data-choices] .choices__list--dropdown {
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.3), 0 -2px 4px -1px rgba(0, 0, 0, 0.2) !important;
        }

        /* Garantir que o dropdown específico do campo agrupar_por funcione */
        [data-field="agrupar_por"].dropdown-upward .choices__list--dropdown,
        [data-field-wrapper="agrupar_por"].dropdown-upward .choices__list--dropdown {
            top: auto !important;
            bottom: 100% !important;
            margin-bottom: 4px !important;
            margin-top: 0 !important;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            border-radius: 6px 6px 6px 6px !important;
        }
    </style>

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
                        Alunos Inscritos
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function adjustDropdownDirection() {
                // Busca pelo campo de agrupamento usando o atributo data-field
                const agruparPorField = document.querySelector('[data-field="agrupar_por"]');
                if (!agruparPorField) {
                    // Fallback: busca por campo wrapper com agrupar_por
                    const fallbackField = document.querySelector('[data-field-wrapper="agrupar_por"]');
                    if (fallbackField) {
                        processField(fallbackField);
                    }
                    return;
                }

                processField(agruparPorField);
            }

            function processField(fieldElement) {
                const choicesContainer = fieldElement.querySelector('[data-choices]');
                if (!choicesContainer) return;

                const dropdownList = choicesContainer.querySelector('.choices__list--dropdown');

                // Observer para detectar quando o dropdown é aberto
                const observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'aria-expanded') {
                            const isOpen = choicesContainer.getAttribute('aria-expanded') === 'true';

                            if (isOpen && dropdownList) {
                                // Aguardar um pouco para o dropdown ser renderizado
                                setTimeout(() => {
                                    // Calcular espaço disponível abaixo
                                    const fieldRect = fieldElement.getBoundingClientRect();
                                    const viewportHeight = window.innerHeight;
                                    const spaceBelow = viewportHeight - fieldRect.bottom;
                                    const dropdownHeight = 200; // Altura estimada do dropdown

                                    // Se não há espaço suficiente abaixo, abrir para cima
                                    if (spaceBelow < dropdownHeight) {
                                        fieldElement.classList.add('dropdown-upward');
                                    } else {
                                        fieldElement.classList.remove('dropdown-upward');
                                    }
                                }, 50);
                            } else {
                                // Remover classe quando fechado
                                fieldElement.classList.remove('dropdown-upward');
                            }
                        }
                    });
                });

                // Observar mudanças no atributo aria-expanded
                observer.observe(choicesContainer, {
                    attributes: true,
                    attributeFilter: ['aria-expanded']
                });

                // Cleanup quando o componente for removido
                window.addEventListener('beforeunload', function () {
                    observer.disconnect();
                });
            }

            // Executar quando a página carregar
            adjustDropdownDirection();

            // Re-executar após updates do Livewire
            document.addEventListener('livewire:navigated', adjustDropdownDirection);
            window.addEventListener('livewire:load', adjustDropdownDirection);

            // Para versões mais recentes do Livewire
            if (window.Livewire) {
                window.Livewire.hook('morph.updated', adjustDropdownDirection);
            }
        });
    </script>
</x-filament-panels::page>