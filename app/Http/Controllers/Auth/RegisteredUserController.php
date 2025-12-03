<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Carbon;
use App\Notifications\SendVerificationCode;

class RegisteredUserController extends Controller
{
    /**
     * Tela de registro.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Registra o usuário, gera o código e envia para a tela de verificação.
     */
    public function store(Request $request): RedirectResponse
    {

        // Validação
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Código para verificação
        $code = rand(100000, 999999);
        $expires_at = Carbon::now()->addMinutes(5);

        // Criando usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_code' => $code,
            'email_verification_code_expires_at' => $expires_at,
        ]);

        // Envia o código por e-mail — mas NÃO deixa quebrar o fluxo
        try {
            $user->notify(new SendVerificationCode($code));
        } catch (\Exception $e) {
            // Aqui evita erro 500 se o Gmail estiver com problema
            // Você pode logar se quiser:
            // \Log::error("Erro ao enviar email: " . $e->getMessage());
        }

        // DESLOGA para permitir acessar a rota guest
        \Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Agora abre a tela de código
        return redirect()->route('verification.code.show', ['email' => $user->email]);
    }
}
