<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\RecebimentoController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Support\Facades\Route;

// Clientes
Route::get('/clientes', [ClienteController::class, 'apiIndex']);
Route::get('/clientes/busca', [ClienteController::class, 'apiBusca']);
Route::post('/clientes', [ClienteController::class, 'apiStore']);
Route::put('/clientes/{id}', [ClienteController::class, 'apiUpdate']);
Route::delete('/clientes/{id}', [ClienteController::class, 'apiDestroy']);

// Produtos
Route::get('/produtos', [ProdutoController::class, 'apiIndex']);
Route::get('/produtos/busca', [ProdutoController::class, 'apiBusca']);
Route::post('/produtos', [ProdutoController::class, 'apiStore']);
Route::put('/produtos/{id}', [ProdutoController::class, 'apiUpdate']);
Route::delete('/produtos/{id}', [ProdutoController::class, 'apiDestroy']);

// Vendas
Route::post('/vendas', [VendaController::class, 'apiStore']);
Route::get('/vendas/busca', [VendaController::class, 'busca']);

// Recebimentos
Route::get('/recebimentos/busca', [RecebimentoController::class, 'busca']);
Route::put('/recebimentos/{id}', [RecebimentoController::class, 'update']);

// Relatórios
Route::get('/relatorios/vendas', [RelatorioController::class, 'vendas']);
Route::post('/relatorios/pdf', [RelatorioController::class, 'gerarPDF']);
Route::get('/relatorios/pdf/{arquivo}', [RelatorioController::class, 'downloadPDF']);

// Dashboard
Route::prefix('dashboard')->group(function () {
    Route::get('/vendas-hoje', function () {
        $total = \App\Models\Venda::whereDate('data', today())->count();
        return response()->json(['total' => $total]);
    });
    
    Route::get('/vendas-mes', function () {
        $total = \App\Models\Venda::whereMonth('data', now()->month)
            ->whereYear('data', now()->year)
            ->count();
        return response()->json(['total' => $total]);
    });
    
    Route::get('/vendas-total', function () {
        $total = \App\Models\Venda::count();
        return response()->json(['total' => $total]);
    });
    
    Route::get('/vendas-por-status', function () {
        $status = \App\Models\Recebimento::select('status', \DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        return response()->json($status);
    });
});