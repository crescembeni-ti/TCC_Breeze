<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 1. Tenta logar
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais estão incorretas.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        // 2. [IMPORTANTE] Checa se o e-mail foi verificado
        if (!$user->hasVerifiedEmail()) {
             // 403 significa "Proibido"
            return response()->json(['message' => 'Por favor, verifique seu e-mail antes de logar.'], 403);
        }

        // 3. Cria o Token (a "chave" de acesso)
        $token = $user->createToken('auth_token_do_app')->plainTextToken;

        // 4. Envia o token e os dados do usuário de volta para o app
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
}