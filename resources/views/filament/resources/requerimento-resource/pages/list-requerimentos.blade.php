<x-filament-panels::page>
</x-filament-panels::page>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-pdf-in-new-tab', (event) => {
            const url = event.url;
            if (url) {
                window.open(url, '_blank');
            }
        });
    });
</script>
