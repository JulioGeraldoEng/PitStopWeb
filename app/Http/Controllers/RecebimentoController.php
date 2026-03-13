<?php

namespace App\Http\Controllers;

use App\Models\Recebimento;
use Illuminate\Http\Request;

class RecebimentoController extends Controller
{
    public function index(Request $request)
    {
        // Se veio com status na URL, passar para a view
        if ($request->has('status')) {
            return view('recebimentos.index', ['filtroStatus' => $request->status]);
        }
        
        return view('recebimentos.index');
    }

    public function busca(Request $request)
    {
        try {
            $query = Recebimento::with('venda.cliente')
                ->join('vendas', 'recebimentos.venda_id', '=', 'vendas.id')
                ->select('recebimentos.*', 'vendas.data as data_venda');

            // Filtro por cliente
            if ($request->filled('clienteId') && $request->clienteId !== 'null') {
                $query->whereHas('venda.cliente', function($q) use ($request) {
                    $q->where('clientes.id', $request->clienteId);
                });
            } elseif ($request->filled('cliente') && trim($request->cliente) !== '') {
                $query->whereHas('venda.cliente', function($q) use ($request) {
                    $q->where('clientes.nome', 'LIKE', '%' . trim($request->cliente) . '%');
                });
            }

            // FILTRO POR STATUS - CORRIGIDO
            if ($request->filled('status') && $request->status !== '') {
                $hoje = now()->format('Y-m-d');
                
                if ($request->status === 'atrasado') {
                    // Atrasados: status pendente E data de vencimento < hoje
                    $query->where('recebimentos.status', 'pendente')
                        ->where('recebimentos.data_vencimento', '<', $hoje);
                } elseif ($request->status === 'pendente') {
                    // Pendentes: status pendente E data de vencimento >= hoje
                    $query->where('recebimentos.status', 'pendente')
                        ->where('recebimentos.data_vencimento', '>=', $hoje);
                } else {
                    // Pago, cancelado: filtro direto
                    $query->where('recebimentos.status', $request->status);
                }
            }

            // Filtro por data da venda
            if ($request->filled('dataInicio') && trim($request->dataInicio) !== '') {
                $dataInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->dataInicio)->format('Y-m-d');
                $query->whereDate('vendas.data', '>=', $dataInicio);
            }

            if ($request->filled('dataFim') && trim($request->dataFim) !== '') {
                $dataFim = \Carbon\Carbon::createFromFormat('d/m/Y', $request->dataFim)->format('Y-m-d');
                $query->whereDate('vendas.data', '<=', $dataFim);
            }

            // Filtro por vencimento
            if ($request->filled('vencimentoInicio') && trim($request->vencimentoInicio) !== '') {
                $vencInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->vencimentoInicio)->format('Y-m-d');
                $query->whereDate('recebimentos.data_vencimento', '>=', $vencInicio);
            }

            if ($request->filled('vencimentoFim') && trim($request->vencimentoFim) !== '') {
                $vencFim = \Carbon\Carbon::createFromFormat('d/m/Y', $request->vencimentoFim)->format('Y-m-d');
                $query->whereDate('recebimentos.data_vencimento', '<=', $vencFim);
            }

            $recebimentos = $query->orderBy('recebimentos.data_vencimento')->get();

            $resultado = $recebimentos->map(function($rec) use ($request) {
                $status = $rec->status;
                $hoje = now()->format('Y-m-d');
                
                // Se for pendente e data de vencimento passada, considerar como atrasado
                if ($status === 'pendente' && $rec->data_vencimento < $hoje) {
                    $status = 'atrasado';
                }
                
                return [
                    'id' => $rec->id,
                    'venda_id' => $rec->venda_id,
                    'cliente' => $rec->venda->cliente->nome ?? 'N/A',
                    'data_venda' => $rec->data_venda,
                    'data_vencimento' => $rec->data_vencimento,
                    'valor_total' => $rec->valor_total,
                    'valor_pago' => $rec->valor_pago,
                    'data_pagamento' => $rec->data_pagamento,
                    'status' => $status, // Status corrigido
                    'forma_pagamento' => $rec->forma_pagamento
                ];
            });

            return response()->json($resultado);
            
        } catch (\Exception $e) {
            \Log::error('Erro na busca de recebimentos: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $recebimento = Recebimento::findOrFail($id);
            
            $dados = [
                'status' => $request->status,
                'valor_pago' => $request->valor_pago,
                'data_pagamento' => $request->data_pagamento,
                'forma_pagamento' => $request->forma_pagamento
            ];

            $recebimento->update($dados);

            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}