<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.throttled', [
                    'seconds' => 60,
                    'minutes' => 1,
                ]),
            ]);
        }

        $data = $this->form->getState();

        if (! Auth::attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        // Verificar se o usuário está aprovado
        if (Auth::user() && !Auth::user()->is_approved) {
            Auth::logout();
            
            throw ValidationException::withMessages([
                'data.email' => 'Sua conta ainda está pendente de aprovação. Aguarde um administrador aprovar seu acesso.',
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
