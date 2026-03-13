<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\ItemVenda;
use App\Models\Recebimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    public function index()
    {
        $vendas = Venda::with('cliente')->orderBy('created_at', 'desc')->get();
        return view('vendas.index', compact('vendas'));
    }

    public function create()
    {
        return view('vendas.form');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Criar a venda
            $venda = Venda::create([
                'cliente_id' => $request->cliente_id,
                'data' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->data)->format('Y-m-d'),
                'data_vencimento' => $request->data_vencimento ? 
                    \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_vencimento)->format('Y-m-d') : null,
                'total' => $request->total ?? collect($request->itens)->sum(fn($item) => $item['quantidade'] * $item['preco_unitario'])
            ]);

            // Adicionar itens
            foreach ($request->itens as $item) {
                ItemVenda::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $item['produto_id'],
                    'nome_produto' => $item['nome'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario']
                ]);
            }

            // Criar recebimento
            Recebimento::create([
                'venda_id' => $venda->id,
                'data_vencimento' => $venda->data_vencimento,
                'valor_total' => $venda->total,
                'status' => 'pendente'
            ]);

            DB::commit();
            
            // Se for requisição AJAX (da API)
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'venda' => $venda]);
            }
            
            return redirect()->route('vendas.index')->with('success', 'Venda registrada com sucesso!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro ao registrar venda: ' . $e->getMessage()], 422);
            }
            
            return back()->with('error', 'Erro ao registrar venda: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Venda $venda)
    {
        $venda->load('cliente', 'itens', 'recebimento');
        return view('vendas.show', compact('venda'));
    }

    public function edit(Venda $venda)
    {
        // Carregar relacionamentos
        $venda->load('cliente', 'itens', 'recebimento');
        
        // Passar a venda para a view
        return view('vendas.form', compact('venda'));
    }

    public function update(Request $request, Venda $venda)
    {
        DB::beginTransaction();
        
        try {
            // Atualizar dados da venda
            $venda->update([
                'cliente_id' => $request->cliente_id,
                'data' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->data)->format('Y-m-d'),
                'data_vencimento' => $request->data_vencimento ? 
                    \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_vencimento)->format('Y-m-d') : null,
                'total' => $request->total ?? collect($request->itens)->sum(fn($item) => $item['quantidade'] * $item['preco_unitario'])
            ]);

            // Remover itens antigos
            $venda->itens()->delete();

            // Adicionar novos itens
            foreach ($request->itens as $item) {
                ItemVenda::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $item['produto_id'],
                    'nome_produto' => $item['nome'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario']
                ]);
            }

            // Atualizar recebimento
            if ($venda->recebimento) {
                $venda->recebimento->update([
                    'data_vencimento' => $request->data_vencimento ? 
                        \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_vencimento)->format('Y-m-d') : null,
                    'valor_total' => $venda->total
                ]);
            }

            DB::commit();
            return redirect()->route('vendas.index')->with('success', 'Venda atualizada com sucesso!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar venda: ' . $e->getMessage());
        }
    }

    public function destroy(Venda $venda)
    {
        DB::beginTransaction();
        try {
            // Excluir itens e recebimento (cascade deve funcionar)
            $venda->delete();
            DB::commit();
            return redirect()->route('vendas.index')->with('success', 'Venda excluída com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir venda: ' . $e->getMessage());
        }
    }

    // ===================== MÉTODOS API =====================
    public function apiStore(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $venda = Venda::create([
                'cliente_id' => $request->cliente_id,
                'data' => $request->data,
                'data_vencimento' => $request->data_vencimento,
                'total' => $request->total
            ]);

            foreach ($request->itens as $item) {
                ItemVenda::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $item['produto_id'],
                    'nome_produto' => $item['nome'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario']
                ]);
            }

            Recebimento::create([
                'venda_id' => $venda->id,
                'data_vencimento' => $request->data_vencimento,
                'valor_total' => $request->total,
                'status' => 'pendente'
            ]);

            DB::commit();
            return response()->json(['success' => true, 'venda' => $venda]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao registrar venda: ' . $e->getMessage()], 422);
        }
    }

    public function busca(Request $request)
    {
        \Log::info('Filtros recebidos:', $request->all()); // Para debug
        
        $query = Venda::with('cliente', 'recebimento');
        
        // FILTRO POR CLIENTE (já funciona)
        if ($request->filled('clienteId') && $request->clienteId !== 'null') {
            $query->where('cliente_id', $request->clienteId);
        }
        
        // FILTRO POR STATUS
        if ($request->filled('status') && $request->status !== '') {
            if ($request->status === 'atrasado') {
                $query->whereHas('recebimento', function($q) {
                    $q->where('status', 'pendente')
                    ->whereDate('data_vencimento', '<', now()->format('Y-m-d'));
                })->orWhereHas('recebimento', function($q) {
                    $q->where('status', 'atrasado');
                });
            } else {
                $query->whereHas('recebimento', function($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }
        }
        
        // FILTRO POR DATA DA VENDA
        if ($request->filled('dataInicio') && trim($request->dataInicio) !== '') {
            try {
                $dataInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->dataInicio)->format('Y-m-d');
                $query->whereDate('data', '>=', $dataInicio);
            } catch (\Exception $e) {
                \Log::error('Erro ao converter dataInicio: ' . $e->getMessage());
            }
        }
        
        if ($request->filled('dataFim') && trim($request->dataFim) !== '') {
            try {
                $dataFim = \Carbon\Carbon::createFromFormat('d/m/Y', $request->dataFim)->format('Y-m-d');
                $query->whereDate('data', '<=', $dataFim);
            } catch (\Exception $e) {
                \Log::error('Erro ao converter dataFim: ' . $e->getMessage());
            }
        }
        
        // FILTRO POR VENCIMENTO
        if ($request->filled('vencimentoInicio') && trim($request->vencimentoInicio) !== '') {
            try {
                $vencInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->vencimentoInicio)->format('Y-m-d');
                $query->whereHas('recebimento', function($q) use ($vencInicio) {
                    $q->whereDate('data_vencimento', '>=', $vencInicio);
                });
            } catch (\Exception $e) {
                \Log::error('Erro ao converter vencimentoInicio: ' . $e->getMessage());
            }
        }
        
        if ($request->filled('vencimentoFim') && trim($request->vencimentoFim) !== '') {
            try {
                $vencFim = \Carbon\Carbon::createFromFormat('d/m/Y', $request->vencimentoFim)->format('Y-m-d');
                $query->whereHas('recebimento', function($q) use ($vencFim) {
                    $q->whereDate('data_vencimento', '<=', $vencFim);
                });
            } catch (\Exception $e) {
                \Log::error('Erro ao converter vencimentoFim: ' . $e->getMessage());
            }
        }
        
        $vendas = $query->orderBy('data', 'desc')->get();
        
        \Log::info('Vendas encontradas: ' . $vendas->count());
        
        $resultado = $vendas->map(function($venda) {
            $recebimento = $venda->recebimento;
            
            // Determinar status
            $status = $recebimento->status ?? 'pendente';
            if ($status === 'pendente' && $recebimento && $recebimento->data_vencimento < now()->format('Y-m-d')) {
                $status = 'atrasado';
            }
            
            return [
                'id' => $venda->id,
                'cliente' => $venda->cliente->nome,
                'data' => $venda->data,
                'vencimento' => $recebimento->data_vencimento ?? null,
                'total' => $venda->total,
                'status' => $status
            ];
        });
        
        return response()->json($resultado);
    }
}