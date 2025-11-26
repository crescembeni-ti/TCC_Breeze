<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tela de login do serviço.
     */
    public function create()
    {
        return view('servico.login'); // você criará esta view
    }

    /**
     * Autenticação do serviço.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('service')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('servico.dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ]);
    }

    /**
     * Logout do serviço.
     */
    public function destroy(Request $request)
    {
        Auth::guard('service')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('servico.login');
    }
}
