<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    protected $code;

    /**
     * Exibe o formulário de edição do perfil.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Atualiza as informações do perfil.
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
     * Exclui a conta do usuário autenticado.
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
