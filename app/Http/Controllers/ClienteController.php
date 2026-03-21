<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'telefone' => [
                'nullable',
                'string',
                'max:20',
                // Validação customizada para SQLite - permite NULL mas força unicidade quando preenchido
                function ($attribute, $value, $fail) use ($request) {
                    if (!empty($value)) {
                        $exists = Cliente::where('telefone', $value)->exists();
                        if ($exists) {
                            $fail('O telefone já está em uso.');
                        }
                    }
                }
            ]
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
            'telefone' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($cliente) {
                    if (!empty($value)) {
                        $exists = Cliente::where('telefone', $value)
                                         ->where('id', '!=', $cliente->id)
                                         ->exists();
                        if ($exists) {
                            $fail('O telefone já está em uso por outro cliente.');
                        }
                    }
                }
            ]
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        try {
            $cliente->delete();
            return redirect()->route('clientes.index')
                             ->with('success', 'Cliente excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('clientes.index')
                             ->with('error', 'Não foi possível excluir o cliente. Ele pode estar vinculado a vendas.');
        }
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
                        ->get(['id', 'nome', 'telefone', 'observacao']);
        return response()->json($clientes);
    }

    public function apiBuscaClientes(Request $request)
    {
        $nome = $request->get('nome');
        
        $query = Cliente::query();
        
        if (!empty($nome)) {
            $query->where('nome', 'LIKE', "%{$nome}%");
        }
        
        $clientes = $query->orderBy('nome')->get();
        
        return response()->json($clientes);
    }

    public function verificarCliente(Request $request)
    {
        $nome = $request->get('nome');
        
        $existe = Cliente::where('nome', $nome)->exists();
        
        return response()->json(['existe' => $existe]);
    }

    public function apiStore(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|max:255',
                'telefone' => [
                    'nullable',
                    'string',
                    'max:20',
                    function ($attribute, $value, $fail) use ($request) {
                        if (!empty($value)) {
                            $exists = Cliente::where('telefone', $value)->exists();
                            if ($exists) {
                                $fail('O telefone já está em uso.');
                            }
                        }
                    }
                ]
            ]);

            $cliente = Cliente::create($request->all());
            return response()->json(['success' => true, 'cliente' => $cliente]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao salvar cliente: ' . $e->getMessage()], 422);
        }
    }

    public function apiUpdate(Request $request, $id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            
            $request->validate([
                'nome' => 'required|max:255',
                'telefone' => [
                    'nullable',
                    'string',
                    'max:20',
                    function ($attribute, $value, $fail) use ($cliente) {
                        if (!empty($value)) {
                            $exists = Cliente::where('telefone', $value)
                                             ->where('id', '!=', $cliente->id)
                                             ->exists();
                            if ($exists) {
                                $fail('O telefone já está em uso por outro cliente.');
                            }
                        }
                    }
                ]
            ]);

            $cliente->update($request->all());
            return response()->json(['success' => true, 'cliente' => $cliente]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar cliente: ' . $e->getMessage()], 422);
        }
    }

    public function apiDestroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            
            // Verificar se o cliente tem vendas antes de excluir
            if ($cliente->vendas()->exists()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Não é possível excluir cliente com vendas vinculadas.'
                ], 422);
            }
            
            $cliente->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao excluir cliente: ' . $e->getMessage()], 422);
        }
    }
}