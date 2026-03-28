<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // TESTE: Se aparecer isso, o middleware está funcionando
        dd('✅ Middleware executado!', session()->all());
        
        if (session()->has('locale')) {
            app()->setLocale(session()->get('locale'));
        }
        
        return $next($request);
    }
}