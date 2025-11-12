<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * 🟩 Exibe a tela de login do administrador.
     * A view usada é: resources/views/admin/auth/login.blade.php
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * 🟩 Faz a autenticação do ADMIN.
     * Usa o "guard" admin (tabela `admins` no banco).
     */
    public function store(Request $request): RedirectResponse
    {
        // 🔹 1. Validação simples dos campos de login
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 🔹 2. Garante que, se o user comum estiver logado, será deslogado
        Auth::guard('web')->logout();

        // 🔹 3. Tenta autenticar usando o guard ADMIN
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            // Regenera sessão por segurança
            $request->session()->regenerate();

            // Redireciona para o painel do admin
            return redirect()->intended(route('admin.dashboard'));
        }

        // 🔹 4. Caso as credenciais estejam erradas
        return back()->withErrors([
            'email' => __('auth.failed'), // Mensagem padrão: "Essas credenciais não correspondem aos nossos registros."
        ])->onlyInput('email');
    }

    /**
     * 🟥 Faz logout do administrador.
     * Encerra apenas a sessão do guard "admin".
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Desloga o admin
        Auth::guard('admin')->logout();

        // Invalida a sessão atual
        $request->session()->invalidate();

        // Gera novo token CSRF
        $request->session()->regenerateToken();

        // Redireciona de volta para o login do admin
        return redirect(route('admin.login'));
    }
}
