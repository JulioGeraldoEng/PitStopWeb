<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está logado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Faça login para continuar.');
        }

        // Verifica se o usuário é admin
        if (Auth::user()->tipo !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Acesso negado! Apenas administradores podem acessar esta área.');
        }

        return $next($request);
    }
}