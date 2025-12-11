<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SendVerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Exibe a tela de registro.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Processa o registro, envia o código e redireciona para verificação.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validação dos dados
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Geração do código de 6 dígitos
        $code = rand(100000, 999999);
        $expires_at = Carbon::now()->addMinutes(5);

        // 3. Criação do Usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_code' => $code,
            'email_verification_code_expires_at' => $expires_at,
        ]);

        // 4. Envio do E-mail (Try/Catch para não travar se o SMTP falhar)
        try {
            $user->notify(new SendVerificationCode($code));
        } catch (\Exception $e) {
            // Se quiser logar o erro: \Log::error('Erro ao enviar email: '.$e->getMessage());
        }

        // 5. CRÍTICO: Deslogar o usuário
        // Isso é necessário porque a rota 'verification.code.show' está no middleware 'guest'
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 6. Redirecionamento passando o e-mail na URL (Query String)
        // Isso vai gerar uma URL tipo: /verificar-codigo?email=usuario@exemplo.com
        return redirect()->route('verification.code.show', ['email' => $user->email]);
    }
}