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

                // Atualizar estoque (opcional)
                // Produto::where('id', $item['produto_id'])->decrement('quantidade', $item['quantidade']);
            }

            // Criar recebimento
            Recebimento::create([
                'venda_id' => $venda->id,
                'data_vencimento' => $request->data_vencimento,
                'valor_total' => $request->total,
                'status' => 'pendente'
            ]);

            DB::commit();
            return redirect()->route('vendas.index')->with('success', 'Venda registrada com sucesso!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao registrar venda: ' . $e->getMessage());
        }
    }

    public function show(Venda $venda)
    {
        $venda->load('cliente', 'itens');
        return view('vendas.show', compact('venda'));
    }

    public function edit(Venda $venda)
    {
        $venda->load('itens');
        return view('vendas.form', compact('venda'));
    }

    public function update(Request $request, Venda $venda)
    {
        // Implementar se necessário
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
}