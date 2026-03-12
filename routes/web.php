<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\RecebimentoController;
use Illuminate\Support\Facades\Route;

// Página inicial redireciona para dashboard se logado, ou login se não
Route::get('/', function () {
    return redirect('/dashboard');
});

// Rotas que precisam de autenticação
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Recursos do sistema (CRUDs)
    Route::resource('clientes', ClienteController::class);
    Route::resource('produtos', ProdutoController::class);
    Route::resource('vendas', VendaController::class);
    Route::resource('recebimentos', RecebimentoController::class);

    Route::resource('produtos', ProdutoController::class);

    Route::resource('vendas', VendaController::class);

    Route::resource('recebimentos', RecebimentoController::class)->only(['index']);

    Route::get('/relatorios', [App\Http\Controllers\RelatorioController::class, 'index'])->name('relatorios.index');
});

require __DIR__.'/auth.php';