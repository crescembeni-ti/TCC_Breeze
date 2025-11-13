<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

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
        // Tenta autenticar
        $request->authenticate();

        $user = $request->user();

        // Se email não verificado → bloco
        if (!$user->hasVerifiedEmail()) {

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Login OK
        $request->session()->regenerate();

        // 🔥 USUÁRIO VAI PRO MAPA (welcome)
        return redirect()->intended(route('home'));
    }

    /**
     * Logout do usuário.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
