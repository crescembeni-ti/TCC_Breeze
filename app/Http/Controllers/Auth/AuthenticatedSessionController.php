<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException; // 1. IMPORTAR O ERRO DE VALIDAÇÃO

class AuthenticatedSessionController extends Controller
{
    /**
     * Exibe a tela de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Lida com a requisição de autenticação.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 2. Tenta autenticar (valida e-mail, senha, etc.)
        $request->authenticate();

        // 3. Pega o usuário que acabou de ser autenticado
        $user = $request->user();

        // 4. [A NOVA REGRA]
        // O usuário é válido, MAS ele já verificou o e-mail?
        if (!$user->hasVerifiedEmail()) {
            
            // 5. Desloga o usuário (para garantir)
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // 6. [A MUDANÇA] Lança um erro de validação padrão.
            // O usuário verá a mesma mensagem de "e-mail ou senha incorretos",
            // fazendo parecer que a conta não existe.
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // 7. Se passou, o usuário ESTÁ verificado. Loga normalmente.
        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destrói a sessão autenticada.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}