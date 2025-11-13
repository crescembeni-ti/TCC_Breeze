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
     * Tela de login do admin.
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Autenticação do admin.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validação
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Se usuário comum estiver logado → desloga
        Auth::guard('web')->logout();

        // Login via guard admin
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            // 🔥 ADMIN TAMBÉM VAI PARA O MAPA (welcome)
            return redirect()->intended(route('home'));
        }

        // Erro nas credenciais
        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }

    /**
     * Logout do admin.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Após logout → login admin
        return redirect(route('admin.login'));
    }
}
