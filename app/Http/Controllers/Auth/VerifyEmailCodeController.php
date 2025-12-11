<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\SendVerificationCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Para erros personalizados

class VerifyEmailCodeController extends Controller
{
    /**
     * Mostra a página para digitar o código.
     */
    public function show(Request $request)
    {
        // Se o e-mail não estiver na URL, manda pro Login (segurança)
        if (!$request->has('email')) {
            return redirect()->route('login');
        }

        return view('auth.verify-code', ['email' => $request->email]);
    }

    /**
     * Valida o código, marca como verificado e LOGA o usuário.
     */
    public function verify(Request $request)
    {
        // 1. Validação dos campos
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'verification_code' => ['required', 'string', 'size:6'], // size:6 é mais estrito que min/max
        ]);

        $user = User::where('email', $request->email)->first();

        // 2. Verifica se o código bate
        if ($request->verification_code !== $user->email_verification_code) {
            throw ValidationException::withMessages([
                'verification_code' => ['O código de verificação está incorreto.'],
            ]);
        }

        // 3. Verifica se expirou
        if (Carbon::now()->gt($user->email_verification_code_expires_at)) {
            return back()->withErrors(['verification_code' => 'O código expirou. Solicite um novo.'])
                         ->withInput($request->only('email'));
        }

        // 4. Se tudo estiver OK, verifica o usuário
        if (!$user->hasVerifiedEmail()) {
            $user->forceFill([
                'email_verified_at' => Carbon::now(),
                'email_verification_code' => null,
                'email_verification_code_expires_at' => null,
            ])->save();
        }

        // 5. LOGIN MANUAL (A mágica acontece aqui)
        Auth::login($user);

        // 6. Segurança: Regenerar ID da sessão para evitar fixação
        $request->session()->regenerate();

        // 7. Redireciona para o Dashboard
        return redirect()->route('dashboard')->with('status', 'E-mail verificado com sucesso! Bem-vindo.');
    }

    /**
     * Reenvia um novo código de verificação.
     */
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        
        $user = User::where('email', $request->email)->first();

        // Se já verificou, manda pro login
        if ($user->hasVerifiedEmail()) {
             return redirect()->route('login')->with('status', 'Este e-mail já foi verificado. Faça login.');
        }

        // Gera novo código
        $code = rand(100000, 999999);
        $expires_at = Carbon::now()->addMinutes(5);

        // Salva
        $user->forceFill([
            'email_verification_code' => $code,
            'email_verification_code_expires_at' => $expires_at,
        ])->save();

        // Envia notificação
        try {
            $user->notify(new SendVerificationCode($code));
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Erro ao enviar e-mail. Tente novamente mais tarde.']);
        }

        return back()->with('status', 'Um novo código foi enviado para o seu e-mail!')
                     ->withInput($request->only('email'));
    }
}