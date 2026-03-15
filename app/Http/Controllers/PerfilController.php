<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    // ==================== PERFIL ====================
    public function edit()
    {
        return view('perfil.index');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta']);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }

    // ==================== CONFIGURAÇÕES ====================
    public function configuracoes()
    {
        return view('perfil.configuracoes');
    }

    public function updateConfiguracoes(Request $request)
    {
        $user = Auth::user();

        // Aqui você pode salvar as configurações em uma tabela separada
        // Por enquanto, vamos apenas simular
        $request->validate([
            'tema' => 'required|in:claro,escuro,auto',
            'idioma' => 'required|in:pt-BR,en,es',
            'notificacoes_email' => 'boolean',
            'notificacoes_sistema' => 'boolean',
            'backup_automatico' => 'boolean',
        ]);

        // Salvar no banco (exemplo - você precisará criar uma tabela settings)
        // Setting::updateOrCreate(
        //     ['user_id' => $user->id],
        //     $request->all()
        // );

        return back()->with('success', 'Configurações salvas com sucesso!');
    }
}