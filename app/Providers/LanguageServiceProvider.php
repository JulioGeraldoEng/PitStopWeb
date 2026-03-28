<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Verificar se a sessão está iniciada antes de usar
        if (session_status() === PHP_SESSION_ACTIVE && Session::has('locale')) {
            $locale = Session::get('locale');
            App::setLocale($locale);
        }
    }
    
    public function register()
    {
        //
    }
}