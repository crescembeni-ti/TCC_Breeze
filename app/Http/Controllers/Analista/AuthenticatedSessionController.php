<?php

namespace App\Http\Controllers\Analista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Importação para facilitar a validação em caso de falha
use Illuminate\Validation\ValidationException; 

class AuthenticatedSessionController extends Controller
{
    /**
     * Exibe o formulário de login do analista.
     */
    public function create()
    {
        // Garante que o usuário só verá a view de login se não estiver logado
        if (Auth::guard('analyst')->check()) {
            return redirect()->route('analyst.dashboard');
        }
        
        return view('analista.login');
    }

    /**
     * Processa a tentativa de autenticação do analista.
     */
    public function store(Request $request)
    {
        // 1. Validação dos campos
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'], // Adicionado string para robustez
            'password' => ['required', 'string'],
        ]);

        // 2. Tentativa de autenticação usando o Guard 'analyst'
        if (Auth::guard('analyst')->attempt($credentials, $request->boolean('remember'))) {
            // Se for bem-sucedido:
            $request->session()->regenerate();
            
            // Redireciona para a rota 'analyst.dashboard', conforme nomeado no routes/web.php
            return redirect()->intended(route('analyst.dashboard')); 
        }

        // 3. Se falhar: Dispara exceção de validação (padrão Breeze/Laravel)
        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas são inválidas para o painel de analista.',
        ]);
    }

    /**
     * Encerra a sessão de autenticação do analista.
     */
    public function destroy(Request $request)
    {
        // 1. Efetua o logout apenas do Guard 'analyst'
        Auth::guard('analyst')->logout();

        // 2. Invalida e regenera o token da sessão
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Redireciona para a rota de login do analista, conforme nomeado no routes/web.php
        return redirect()->route('analista.login'); 
    }
}