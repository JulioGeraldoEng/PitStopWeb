<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\RecebimentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\WhatsAppController;
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

// WhatsApp
Route::post('/whatsapp/conectar', [App\Http\Controllers\WhatsAppController::class, 'conectar']);
Route::post('/whatsapp/reiniciar', [App\Http\Controllers\WhatsAppController::class, 'reiniciar']);
Route::get('/whatsapp/status', [App\Http\Controllers\WhatsAppController::class, 'status']);
Route::post('/whatsapp/enviar-atrasados', [App\Http\Controllers\WhatsAppController::class, 'enviarAtrasados']);
Route::post('/whatsapp/logout', [WhatsAppController::class, 'logout']);

// Dashboard
Route::prefix('dashboard')->group(function () {
    
    // Total de vendas
    Route::get('/vendas-total', function () {
        $total = \App\Models\Venda::count();
        return response()->json(['total' => $total]);
    });
    
    // Vendas no mês
    Route::get('/vendas-mes', function () {
        $total = \App\Models\Venda::whereMonth('data', now()->month)
            ->whereYear('data', now()->year)
            ->count();
        return response()->json(['total' => $total]);
    });
    
    // Vendas por status (CORRIGIDO)
    Route::get('/vendas-por-status', function () {
        $hoje = now()->format('Y-m-d');
        
        $pagos = \App\Models\Recebimento::where('status', 'pago')->count();
        $cancelados = \App\Models\Recebimento::where('status', 'cancelado')->count();
        
        // Pendentes com data >= hoje
        $pendentes = \App\Models\Recebimento::where('status', 'pendente')
            ->where('data_vencimento', '>=', $hoje)
            ->count();
        
        // Atrasados (pendentes com data < hoje)
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
    
    // Top produtos mais vendidos
    Route::get('/top-produtos', function () {
        $topProdutos = \App\Models\ItemVenda::select(
                'produto_id',
                \DB::raw('SUM(quantidade) as total_quantidade'),
                \DB::raw('SUM(quantidade * preco_unitario) as total_vendas')
            )
            ->with('produto')
            ->groupBy('produto_id')
            ->orderBy('total_vendas', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'nome' => $item->produto->nome ?? 'Produto',
                    'quantidade' => $item->total_quantidade,
                    'total' => $item->total_vendas
                ];
            });
        
        return response()->json($topProdutos);
    });
    
    // Top clientes
    Route::get('/top-clientes', function () {
        $topClientes = \App\Models\Venda::select(
                'cliente_id',
                \DB::raw('COUNT(*) as total_vendas'),
                \DB::raw('SUM(total) as total_gasto')
            )
            ->with('cliente')
            ->groupBy('cliente_id')
            ->orderBy('total_gasto', 'desc')
            ->limit(5)
            ->get()
            ->map(function($venda) {
                return [
                    'nome' => $venda->cliente->nome ?? 'Cliente',
                    'total_vendas' => $venda->total_vendas,
                    'total_gasto' => $venda->total_gasto
                ];
            });
        
        return response()->json($topClientes);
    });
    
    // Últimas vendas
    Route::get('/ultimas-vendas', function () {
        $ultimasVendas = \App\Models\Venda::with('cliente', 'recebimento')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($venda) {
                $recebimento = $venda->recebimento;
                $status = $recebimento->status ?? 'pendente';
                
                if ($status === 'pendente' && $recebimento && $recebimento->data_vencimento < now()->format('Y-m-d')) {
                    $status = 'atrasado';
                }
                
                return [
                    'id' => $venda->id,
                    'cliente' => $venda->cliente->nome ?? 'N/A',
                    'data' => $venda->data,
                    'vencimento' => $recebimento->data_vencimento ?? null,
                    'total' => $venda->total,
                    'status' => $status
                ];
            });
        
        return response()->json($ultimasVendas);
    });
    
    // Recebimentos do dia
    Route::get('/recebimentos-dia', function () {
        $hoje = now()->format('Y-m-d');
        $total = \App\Models\Recebimento::whereDate('data_pagamento', $hoje)
            ->where('status', 'pago')
            ->sum('valor_pago');
        
        return response()->json(['total' => $total]);
    });
});