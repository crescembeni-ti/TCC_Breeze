<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('servico.login');
    }

    public function store(Request $request)
    {
        // DESLOGA TODOS OS OUTROS GUARDS
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        Auth::guard('analyst')->logout();

        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('service')->attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            return redirect()->route('service.dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('service')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('service.login');
    }
}
