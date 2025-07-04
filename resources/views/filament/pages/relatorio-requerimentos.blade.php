<x-filament-panels::page>
    <!-- Seção Superior - Relatórios Gerais com Filtro de Data -->
    <div class="mb-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Relatórios Gerais por Período
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Selecione o período para gerar os relatórios gerais de 2ª chamada.
            </p>

            <form wire:submit.prevent="generateRelatoriosGerais">
                {{ $this->formGeral }}

                <div class="mt-6 flex gap-4">
                    <x-filament::button type="button" wire:click="generateRelatorioPorSerie">
                        Gerar Relatório Geral
                    </x-filament::button>

                    <x-filament::button type="button" wire:click="generateRelatorioCompleto">
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
                        Configure filtros específicos para gerar um relatório personalizado.
                    </p>
                </div>
                <div>
                    {{ ($this->filtrosAction)(['size' => 'sm']) }}
                </div>
            </div>

            <form wire:submit.prevent="generateReport">
                {{ $this->form }}

                <div class="mt-6">
                    <x-filament::button type="submit">
                        Gerar Relatório Personalizado
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>