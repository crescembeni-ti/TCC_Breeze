<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Sessão Autenticada para Administradores.
 * Gerencia o ciclo de vida do login administrativo:
 * 1. Exibição do formulário de login específico.
 * 2. Autenticação via Guard 'admin'.
 * 3. Logout e invalidação de sessão.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Exibe a tela de login exclusiva para administradores.
     */
    public function create()
    {
        return view('admin.auth.login');
    }

    /**
     * Processa a tentativa de login administrativo.
     * 
     * Blocos de lógica:
     * - Isolamento de Sessão: Desloga todos os outros tipos de usuários (Cidadão, Analista, Serviço) 
     *   para garantir que o navegador mantenha apenas a sessão administrativa ativa.
     * - Autenticação: Utiliza o Guard 'admin' para validar as credenciais no banco de dados.
     * - Segurança: Regenera a sessão após o login para prevenir ataques de fixação de sessão.
     */
    public function store(Request $request)
    {
        // Limpa sessões de outros perfis para evitar conflitos de permissão
        Auth::guard('web')->logout();
        Auth::guard('analyst')->logout();
        Auth::guard('service')->logout();

        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        // Tenta autenticar no guard específico de administradores
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    /**
     * Encerra a sessão administrativa.
     * 
     * Blocos de lógica:
     * - Logout: Sai do guard 'admin'.
     * - Limpeza: Invalida a sessão atual e regenera o token CSRF para segurança.
     */
    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
