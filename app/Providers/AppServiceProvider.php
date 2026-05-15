<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        app()->setLocale('pt_BR');

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): HtmlString => new HtmlString(<<<'HTML'
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
            HTML),
        );
    }
}
