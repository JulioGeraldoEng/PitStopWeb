<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\ItemVenda;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('relatorios.index');
    }

    public function vendas(Request $request)
    {
        try {
            // Query base - usar leftJoin para garantir que todas as vendas apareçam
            $query = Venda::with(['cliente', 'itens', 'recebimento'])
                ->select('vendas.*', 'clientes.nome as cliente_nome', 'clientes.telefone as cliente_telefone', 
                         'clientes.observacao as cliente_observacao', 
                         'recebimentos.status as recebimento_status', 
                         'recebimentos.data_vencimento as recebimento_vencimento')
                ->join('clientes', 'vendas.cliente_id', '=', 'clientes.id')
                ->leftJoin('recebimentos', 'vendas.id', '=', 'recebimentos.venda_id');

            // 🔥 DEBUG: Log dos parâmetros recebidos (opcional, remover depois)
            \Log::info('Parâmetros do relatório:', $request->all());

            // Filtro por cliente - CORRIGIDO
            if ($request->filled('clienteId') && $request->clienteId !== 'null' && $request->clienteId !== '') {
                $query->where('clientes.id', $request->clienteId);
            } elseif ($request->filled('cliente') && trim($request->cliente) !== '') {
                $query->where('clientes.nome', 'LIKE', '%' . trim($request->cliente) . '%');
            }

            // Filtro por status - CORRIGIDO
            if ($request->filled('status') && $request->status !== '') {
                if ($request->status === 'atrasado') {
                    $query->where(function($q) {
                        $q->where('recebimentos.status', 'pendente')
                          ->whereDate('recebimentos.data_vencimento', '<', now()->format('Y-m-d'));
                    })->orWhere('recebimentos.status', 'atrasado');
                } elseif ($request->status === 'pendente') {
                    $query->where('recebimentos.status', 'pendente')
                          ->whereDate('recebimentos.data_vencimento', '>=', now()->format('Y-m-d'));
                } else {
                    $query->where('recebimentos.status', $request->status);
                }
            }

            // Filtro por data da venda - CORRIGIDO
            if ($request->filled('dataInicio') && trim($request->dataInicio) !== '') {
                try {
                    $dataInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->dataInicio)->format('Y-m-d');
                    $query->whereDate('vendas.data', '>=', $dataInicio);
                } catch (\Exception $e) {
                    // Data inválida, ignora o filtro
                }
            }

            if ($request->filled('dataFim') && trim($request->dataFim) !== '') {
                try {
                    $dataFim = \Carbon\Carbon::createFromFormat('d/m/Y', $request->dataFim)->format('Y-m-d');
                    $query->whereDate('vendas.data', '<=', $dataFim);
                } catch (\Exception $e) {
                    // Data inválida, ignora o filtro
                }
            }

            // Filtro por vencimento - CORRIGIDO
            if ($request->filled('vencimentoInicio') && trim($request->vencimentoInicio) !== '') {
                try {
                    $vencInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->vencimentoInicio)->format('Y-m-d');
                    $query->whereDate('recebimentos.data_vencimento', '>=', $vencInicio);
                } catch (\Exception $e) {
                    // Data inválida, ignora o filtro
                }
            }

            if ($request->filled('vencimentoFim') && trim($request->vencimentoFim) !== '') {
                try {
                    $vencFim = \Carbon\Carbon::createFromFormat('d/m/Y', $request->vencimentoFim)->format('Y-m-d');
                    $query->whereDate('recebimentos.data_vencimento', '<=', $vencFim);
                } catch (\Exception $e) {
                    // Data inválida, ignora o filtro
                }
            }

            // 🔥 DEBUG: Log da SQL (opcional, remover depois)
            \Log::info('SQL: ' . $query->toSql());
            \Log::info('Bindings: ', $query->getBindings());

            $vendas = $query->orderBy('vendas.data', 'desc')->get();

            // 🔥 DEBUG: Quantidade de vendas encontradas
            \Log::info('Vendas encontradas: ' . $vendas->count());

            $resultado = $vendas->map(function($venda) {
                // Determinar status
                $status = $venda->recebimento_status ?? 'pendente';
                
                // Se for pendente, verificar se está atrasado
                if ($status === 'pendente' && $venda->recebimento_vencimento) {
                    if ($venda->recebimento_vencimento < now()->format('Y-m-d')) {
                        $status = 'atrasado';
                    }
                }

                return [
                    'venda_id' => $venda->id,
                    'cliente' => $venda->cliente_nome ?? 'N/A',
                    'telefone' => $venda->cliente_telefone ?? '',
                    'observacao' => $venda->cliente_observacao ?? '',
                    'data' => $venda->data,
                    'vencimento' => $venda->recebimento_vencimento,
                    'total_venda' => $venda->total,
                    'status_pagamento' => $status,
                    'itens' => $venda->itens->map(function($item) {
                        return [
                            'nome_produto' => $item->nome_produto,
                            'quantidade' => $item->quantidade,
                            'preco_unitario' => $item->preco_unitario
                        ];
                    })
                ];
            });

            return response()->json($resultado);

        } catch (\Exception $e) {
            // Log do erro
            \Log::error('Erro no relatório: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function gerarPDF(Request $request)
    {
        try {
            $html = $request->html;
            
            // Gerar PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'landscape');
            
            // Salvar arquivo temporário
            $filename = 'relatorio_' . time() . '.pdf';
            $path = storage_path('app/public/' . $filename);
            $pdf->save($path);
            
            return response()->json([
                'success' => true,
                'arquivo' => $filename
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function downloadPDF($arquivo)
    {
        $path = storage_path('app/public/' . $arquivo);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $arquivo . '"'
        ]);
    }
}