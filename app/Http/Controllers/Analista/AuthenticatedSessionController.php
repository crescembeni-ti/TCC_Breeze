<?php

namespace App\Http\Controllers\Analista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('analista.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('analyst')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('analista.dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::guard('analyst')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('analista.login');
    }
}
