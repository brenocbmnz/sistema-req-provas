<?php

namespace App\Providers;

use App\Filament\Pages\AprovarUsuarios;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Illuminate\Support\ServiceProvider;

class UserMenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Filament::serving(function () {
            Filament::registerUserMenuItems([
                UserMenuItem::make()
                    ->label('Aprovar Usuários')
                    ->url(fn () => AprovarUsuarios::getUrl())
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn () => User::pendingApproval()->exists()),
            ]);
        });
    }
}
