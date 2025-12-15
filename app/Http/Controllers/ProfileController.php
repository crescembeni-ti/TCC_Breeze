<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash; // IMPORTANTE

class ProfileController extends Controller
{
    /**
     * Exibe o formulário de perfil
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Atualiza nome e email
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user->update($request->only('name', 'email'));

        return Redirect::route('profile.edit')
            ->with('status', 'Perfil atualizado com sucesso!');
    }

    /**
     * 🔐 TROCA DE SENHA DO USUÁRIO LOGADO
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        // =========================
        // VALIDAÇÃO DOS CAMPOS
        // =========================
        $request->validate([
            'current_password' => ['required'],              // senha atual obrigatória
            'password' => ['required', 'confirmed', 'min:8'],// nova senha
        ], [
            'current_password.required' => 'Informe sua senha atual.',
            'password.min' => 'A nova senha deve conter pelo menos 8 caracteres.',
            'password.confirmed' => 'As senhas não conferem.',
        ]);

        // =========================
        // CONFERE A SENHA ATUAL
        // =========================
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'A senha atual está incorreta.',
            ]);
        }

        // =========================
        // ATUALIZA A SENHA
        // =========================
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Senha alterada com sucesso!');
    }

    /**
     * Exclui a conta
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ]);

        $user = $request->user();

        if (!Auth::attempt([
            'email'    => $user->email,
            'password' => $request->password,
        ])) {
            return back()->withErrors([
                'password' => 'A senha informada está incorreta.',
            ]);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect('/')
            ->with('status', 'Conta excluída com sucesso.');
    }
}
