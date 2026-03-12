<?php

namespace App\Http\Controllers;

use App\Models\Recebimento;
use Illuminate\Http\Request;

class RecebimentoController extends Controller
{
    public function index()
    {
        return view('recebimentos.index');
    }

    public function busca(Request $request)
    {
        try {
            $query = Recebimento::with('venda.cliente')
                ->join('vendas', 'recebimentos.venda_id', '=', 'vendas.id')
                ->select('recebimentos.*', 'vendas.data as data_venda');

            // 🔥 CORREÇÃO: Só aplicar filtro de cliente se tiver algo preenchido
            if ($request->filled('clienteId') && $request->clienteId !== 'null' && $request->clienteId !== '') {
                $query->whereHas('venda.cliente', function($q) use ($request) {
                    $q->where('clientes.id', $request->clienteId);
                });
            } elseif ($request->filled('cliente') && trim($request->cliente) !== '') {
                $query->whereHas('venda.cliente', function($q) use ($request) {
                    $q->where('clientes.nome', 'LIKE', '%' . trim($request->cliente) . '%');
                });
            }

            // 🔥 CORREÇÃO: Só aplicar filtro de status se tiver algo preenchido
            if ($request->filled('status') && $request->status !== '') {
                if ($request->status === 'atrasado') {
                    $query->where(function($q) {
                        $q->where('recebimentos.status', 'pendente')
                        ->where('recebimentos.data_vencimento', '<', now()->format('Y-m-d'));
                    })->orWhere('recebimentos.status', 'atrasado');
                } elseif ($request->status === 'pendente') {
                    $query->where('recebimentos.status', 'pendente')
                        ->where('recebimentos.data_vencimento', '>=', now()->format('Y-m-d'));
                } else {
                    $query->where('recebimentos.status', $request->status);
                }
            }

            // 🔥 IMPORTANTE: Ordenar por vencimento
            $recebimentos = $query->orderBy('recebimentos.data_vencimento')->get();

            // 🔥 DEBUG: Verificar quantos registros foram encontrados (opcional)
            \Log::info('Total de recebimentos encontrados: ' . $recebimentos->count());

            $resultado = $recebimentos->map(function($rec) {
                return [
                    'id' => $rec->id,
                    'venda_id' => $rec->venda_id,
                    'cliente' => $rec->venda->cliente->nome ?? 'N/A',
                    'data_venda' => $rec->data_venda,
                    'data_vencimento' => $rec->data_vencimento,
                    'valor_total' => $rec->valor_total,
                    'valor_pago' => $rec->valor_pago,
                    'data_pagamento' => $rec->data_pagamento,
                    'status' => $rec->status,
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