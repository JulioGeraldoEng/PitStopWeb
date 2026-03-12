<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|max:255',
            'telefone' => 'nullable|unique:clientes,telefone'
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente criado com sucesso!');
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.form', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nome' => 'required|max:255',
            'telefone' => 'nullable|unique:clientes,telefone,' . $cliente->id
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente excluído com sucesso!');
    }

        // ===================== MÉTODOS PARA API (AJAX) =====================
    public function apiIndex()
    {
        return response()->json(Cliente::orderBy('nome')->get());
    }

    public function apiBusca(Request $request)
    {
        $termo = $request->get('termo');
        $clientes = Cliente::where('nome', 'LIKE', "%{$termo}%")
                           ->orderBy('nome')
                           ->get(['id', 'nome']);
        return response()->json($clientes);
    }

    public function apiStore(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|max:255',
                'telefone' => 'nullable|unique:clientes,telefone'
            ]);

            $cliente = Cliente::create($request->all());
            return response()->json(['success' => true, 'cliente' => $cliente]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao salvar cliente.'], 422);
        }
    }

    public function apiUpdate(Request $request, $id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            
            $request->validate([
                'nome' => 'required|max:255',
                'telefone' => 'nullable|unique:clientes,telefone,' . $id
            ]);

            $cliente->update($request->all());
            return response()->json(['success' => true, 'cliente' => $cliente]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar cliente.'], 422);
        }
    }

    public function apiDestroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $cliente->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao excluir cliente.'], 422);
        }
    }
}