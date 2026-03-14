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
Route::get('/clientes/busca-clientes', [ClienteController::class, 'apiBuscaClientes']);
Route::get('/clientes/verificar', [ClienteController::class, 'verificarCliente']);
Route::post('/clientes', [ClienteController::class, 'apiStore']);
Route::put('/clientes/{id}', [ClienteController::class, 'apiUpdate']);
Route::delete('/clientes/{id}', [ClienteController::class, 'apiDestroy']);

// Produtos
Route::get('/produtos', [ProdutoController::class, 'apiIndex']);
Route::get('/produtos/busca', [ProdutoController::class, 'apiBusca']);                // Para autocomplete
Route::get('/produtos/busca-produtos', [ProdutoController::class, 'apiBuscaProdutos']); // Para busca com filtros
Route::post('/produtos', [ProdutoController::class, 'apiStore']);
Route::put('/produtos/{id}', [ProdutoController::class, 'apiUpdate']);
Route::delete('/produtos/{id}', [ProdutoController::class, 'apiDestroy']);
Route::get('/produtos/verificar', [ProdutoController::class, 'verificarProduto']);

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
        $hoje = now()->format('Y-m-d');
        
        $pagos = \App\Models\Recebimento::where('status', 'pago')->count();
        $cancelados = \App\Models\Recebimento::where('status', 'cancelado')->count();
        $pendentes = \App\Models\Recebimento::where('status', 'pendente')
            ->where('data_vencimento', '>=', $hoje)
            ->count();
        $atrasados = \App\Models\Recebimento::where('status', 'pendente')
            ->where('data_vencimento', '<', $hoje)
            ->count();
        
        return response()->json([
            'pago' => $pagos,
            'pendente' => $pendentes,
            'atrasado' => $atrasados,
            'cancelado' => $cancelados
        ]);
    });
});