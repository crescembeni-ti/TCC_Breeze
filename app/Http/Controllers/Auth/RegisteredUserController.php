<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Carbon; // 1. IMPORTAR CARBON (para o tempo)
use App\Notifications\SendVerificationCode; // 2. IMPORTAR A NOVA NOTIFICAÇÃO

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
     * Registra um novo usuário, envia o CÓDIGO e redireciona para a TELA DE CÓDIGO.
     */
    public function store(Request $request): RedirectResponse
    {
        // 3. Validação (está correta com o 'email:rfc,dns')
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 4. Gera o código e o tempo de expiração
        $code = rand(100000, 999999); // Gera um código de 6 dígitos
        $expires_at = Carbon::now()->addMinutes(5);

        // 5. Cria o usuário com os novos campos de verificação
        // --- CORREÇÃO AQUI ---
        // Usando os nomes corretos das colunas do seu banco de dados
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_code' => $code, // Nome corrigido
            'email_verification_code_expires_at' => $expires_at, // Nome corrigido
        ]);
        // --- FIM DA CORREÇÃO ---

        // 6. Envia o e-mail com o CÓDIGO
        // (Certifique-se que o nome da sua notificação está correto)
        $user->notify(new SendVerificationCode($code));

        // 7. [REMOVIDO] event(new Registered($user));
        // 8. [REMOVIDO] Auth::login($user);

        // 9. Redireciona para a NOVA tela de verificação de CÓDIGO,
        // passando o e-mail na URL.
        return redirect()->route('verification.code.notice', ['email' => $user->email]);
    }
}