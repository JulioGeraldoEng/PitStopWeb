<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    // ===================== MÉTODOS WEB =====================
    public function index()
    {
        $produtos = Produto::orderBy('nome')->get();
        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        return view('produtos.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|max:255',
            'preco' => 'required|numeric|min:0',
            'quantidade' => 'required|integer|min:0'
        ]);

        Produto::create($request->all());

        return redirect()->route('produtos.index')
                         ->with('success', 'Produto criado com sucesso!');
    }

    public function edit(Produto $produto)
    {
        return view('produtos.form', compact('produto'));
    }

    public function update(Request $request, Produto $produto)
    {
        $request->validate([
            'nome' => 'required|max:255',
            'preco' => 'required|numeric|min:0',
            'quantidade' => 'required|integer|min:0'
        ]);

        $produto->update($request->all());

        return redirect()->route('produtos.index')
                         ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();

        return redirect()->route('produtos.index')
                         ->with('success', 'Produto excluído com sucesso!');
    }

    // ===================== MÉTODOS PARA API (AJAX) =====================
    public function apiIndex()
    {
        return response()->json(Produto::orderBy('nome')->get());
    }

    public function apiBusca(Request $request)
    {
        $termo = $request->get('termo');
        $produtos = Produto::where('nome', 'LIKE', "%{$termo}%")
                           ->orderBy('nome')
                           ->get(['id', 'nome', 'preco']);
        return response()->json($produtos);
    }

    public function apiStore(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|max:255',
                'preco' => 'required|numeric|min:0',
                'quantidade' => 'required|integer|min:0'
            ]);

            $produto = Produto::create($request->all());
            return response()->json(['success' => true, 'produto' => $produto]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao salvar produto.'], 422);
        }
    }

    public function apiUpdate(Request $request, $id)
    {
        try {
            $produto = Produto::findOrFail($id);
            
            $request->validate([
                'nome' => 'required|max:255',
                'preco' => 'required|numeric|min:0',
                'quantidade' => 'required|integer|min:0'
            ]);

            $produto->update($request->all());
            return response()->json(['success' => true, 'produto' => $produto]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar produto.'], 422);
        }
    }

    public function apiDestroy($id)
    {
        try {
            $produto = Produto::findOrFail($id);
            $produto->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao excluir produto.'], 422);
        }
    }
}