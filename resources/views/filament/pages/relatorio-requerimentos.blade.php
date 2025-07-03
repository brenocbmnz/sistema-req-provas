<x-filament-panels::page>
    <form wire:submit.prevent="generateReport">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Gerar Relatório em PDF
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>