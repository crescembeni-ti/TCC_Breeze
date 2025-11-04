<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use App\Models\User; // 1. IMPORTAR User
use App\Notifications\SendVerificationCode; // 2. IMPORTAR Notificação
use Illuminate\Support\Carbon; // 3. IMPORTAR Carbon
use Illuminate\Support\Facades\Auth; // 4. IMPORTAR Auth

class VerifyEmailCodeController extends Controller
{
    /**
     * Mostra a página para digitar o código.
     */
    public function show(Request $request)
    {
        // Se o e-mail não estiver na URL, redireciona para o registro
        if (!$request->has('email')) {
            return redirect()->route('register');
        }
        return view('auth.verify-code', ['email' => $request->email]);
    }

    /**
     * Valida o código de verificação de e-mail.
     * (Método 100% corrigido)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'verification_code' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        // --- CORREÇÃO 1: LINHA DE DEBUG REMOVIDA ---
        // dd(...) foi removido daqui.

        // --- CORREÇÃO 2: USANDO NOME DE COLUNA CORRETO ---
        if (!$user || $request->verification_code != $user->email_verification_code) {
            return back()->withErrors(['verification_code' => 'O código de verificação está incorreto.'])
                         ->withInput($request->only('email'));
        }

        // --- CORREÇÃO 3: USANDO NOME DE COLUNA CORRETO ---
        if (now()->gt($user->email_verification_code_expires_at)) {
            // Limpa o código antigo para forçar o reenvio
            $user->email_verification_code = null;
            $user->email_verification_code_expires_at = null;
            $user->save();
            
            return back()->withErrors(['verification_code' => 'O código expirou. Solicite um novo.'])
                         ->withInput($request->only('email'));
        }

        // 3. O usuário já está verificado?
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // 4. Marcar como verificado (e limpar os campos)
        $user->email_verified_at = Carbon::now();
        // --- CORREÇÃO 4: USANDO NOMES DE COLUNA CORRETOS ---
        $user->email_verification_code = null;
        $user->email_verification_code_expires_at = null;
        $user->save();
        
        // 5. Logar o usuário manualmente
        Auth::login($user);

        // 6. Redirecionar para o dashboard
        return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
    }

    /**
     * Reenvia um novo código de verificação.
     */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Usuário não encontrado.']);
        }

        if ($user->hasVerifiedEmail()) {
             return redirect()->route('login')->withErrors(['email' => 'Este e-mail já foi verificado.']);
        }

        $code = rand(100000, 999999);
        $expires_at = Carbon::now()->addMinutes(5);

        // --- CORREÇÃO 5: USANDO NOMES DE COLUNA CORRETOS ---
        $user->email_verification_code = $code;
        $user->email_verification_code_expires_at = $expires_at;
        $user->save();

        $user->notify(new SendVerificationCode($code));

        return back()->with('status', 'Um novo código de verificação foi enviado para o seu e-mail.')
                     ->withInput($request->only('email'));
    }
}