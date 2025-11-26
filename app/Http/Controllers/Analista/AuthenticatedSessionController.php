<?php

namespace App\Http\Controllers\Analista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        if (Auth::guard('analyst')->check()) {
            return redirect()->route('analyst.dashboard');
        }

        return view('analista.login');
    }

    public function store(Request $request)
    {
        // DESLOGA TODOS OS OUTROS GUARDS
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        Auth::guard('service')->logout();

        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('analyst')->attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            return redirect()->intended(route('analyst.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas são inválidas para o painel de analista.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('analyst')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('analyst.login');
    }
}
