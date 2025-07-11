<?php

namespace App\Observers;

use App\Events\UserRegistered;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Se o usuário não tem o campo is_approved definido explicitamente, define como false
        // Isso evita sobrescrever usuários que foram criados já aprovados
        if ($user->is_approved === null || !$user->wasRecentlyCreated || !isset($user->getAttributes()['is_approved'])) {
            // Só atualiza se não foi explicitamente definido como aprovado
            if (!$user->is_approved) {
                $user->updateQuietly(['is_approved' => false]);
            }
        }
        
        // Disparar evento de usuário registrado apenas se não for aprovado
        if (!$user->is_approved) {
            event(new UserRegistered($user));
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
