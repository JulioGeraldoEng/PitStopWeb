<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        $allowedLocales = ['pt-BR', 'en', 'es'];
        
        if (in_array($locale, $allowedLocales)) {
            // Salvar na sessão
            Session::put('locale', $locale);
            
            // Aplicar o idioma globalmente
            app()->setLocale($locale);
            
            session()->flash('success', 'Idioma alterado para ' . $locale);
        } else {
            session()->flash('error', 'Idioma não suportado.');
        }
        
        return redirect()->back();
    }
}