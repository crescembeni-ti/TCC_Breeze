<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Tenta autenticar (valida e-mail, senha e rate limit)
        $request->authenticate();

        // 2. Se autenticou, pega o usuário
        $user = $request->user();

        // 3. [A CHECAGEM] O usuário está logando, mas AINDA não se verificou?
        if (!$user->hasVerifiedEmail()) {
            
            // Pega o e-mail para enviar à próxima tela
            $email = $user->email;
            
            // 4. Desloga o usuário (ele não deve ter uma sessão)
            Auth::guard('web')->logout();

            // 5. Redireciona para a tela de INSERIR O CÓDIGO,
            // enviando um erro para o campo 'email'.
            return redirect()->route('verification.code.notice', ['email' => $email])
                ->withErrors(['email' => 'Você precisa verificar seu e-mail antes de logar. Por favor, insira o código que enviamos.']);
        }

        // 6. Se passou, o usuário está verificado. Loga normalmente.
        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    // ... (o método 'destroy' continua o mesmo) ...


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
