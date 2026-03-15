<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\RecebimentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\SobreController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

// Página inicial redireciona para dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Rotas que precisam de autenticação
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Perfil (do Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // NOVO: Perfil personalizado
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/senha', [PerfilController::class, 'updatePassword'])->name('perfil.password');

    Route::get('/configuracoes', [PerfilController::class, 'configuracoes'])->name('configuracoes');
    Route::put('/configuracoes', [PerfilController::class, 'updateConfiguracoes'])->name('configuracoes.update');

    // Recursos do sistema (CRUDs)
    Route::resource('clientes', ClienteController::class);
    Route::resource('produtos', ProdutoController::class);
    Route::resource('vendas', VendaController::class);
    Route::resource('recebimentos', RecebimentoController::class)->only(['index']);
    
    // Relatórios
    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    
    // WhatsApp
    // WhatsApp
    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    
    // Sobre Route::get('/sobre', function () { return view('sobre.index'); })->name('sobre.index');

    Route::get('/sobre', function () {
        return view('sobre.index');
    })->name('sobre.index')->middleware('auth');
});

require __DIR__.'/auth.php';