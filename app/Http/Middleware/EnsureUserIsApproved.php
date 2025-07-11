<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Só verificar se o usuário está autenticado e está acessando uma rota protegida
        if (Auth::check() && !Auth::user()->is_approved) {
            // Se estiver tentando acessar uma rota admin que não seja login ou registro
            if ($request->is('admin') || ($request->is('admin/*') && !$request->is('admin/login') && !$request->is('admin/register'))) {
                Auth::logout();
                
                return redirect('/admin/login')
                    ->with('error', 'Sua conta ainda está pendente de aprovação. Aguarde um administrador aprovar seu acesso.');
            }
        }

        return $next($request);
    }
}
