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

// Recebimentos
Route::get('/recebimentos/busca', [RecebimentoController::class, 'busca']);
Route::put('/recebimentos/{id}', [RecebimentoController::class, 'update']);

// Relatórios
Route::get('/relatorios/vendas', [RelatorioController::class, 'vendas']);
Route::post('/relatorios/pdf', [RelatorioController::class, 'gerarPDF']);
Route::get('/relatorios/pdf/{arquivo}', [RelatorioController::class, 'downloadPDF']);