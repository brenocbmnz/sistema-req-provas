<?php

namespace App\Providers;

use App\Events\UserRegistered;
use App\Listeners\NotifyAdminsOfNewUser;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale('pt_BR');
        
        // Registrar observers
        User::observe(UserObserver::class);
        
        // Registrar listeners
        Event::listen(UserRegistered::class, NotifyAdminsOfNewUser::class);
    }
}
