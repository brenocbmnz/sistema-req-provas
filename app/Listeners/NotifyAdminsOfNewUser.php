<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminsOfNewUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // Notificar todos os usuários aprovados sobre o novo usuário
        $approvedUsers = User::approved()->get();
        
        foreach ($approvedUsers as $user) {
            $user->notify(new NewUserRegistered($event->user));
        }
    }
}
