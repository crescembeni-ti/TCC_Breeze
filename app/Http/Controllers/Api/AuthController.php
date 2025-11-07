<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

// --- Imports Adicionados para o Registro ---
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Lida com a tentativa de login da API
     */
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

        // 2. Pega o usuário
        $user = User::where('email', $request->email)->first();

        // --- CORREÇÃO ---
        // Removi a checagem 'hasVerifiedEmail()'.
        // Nosso método 'register' (abaixo) não envia verificação,
        // então essa checagem iria bloquear usuários recém-cadastrados.
        
        // 3. Cria o Token (a "chave" de acesso)
        $token = $user->createToken('auth_token_do_app')->plainTextToken;

        // 4. Envia o token e os dados do usuário de volta para o app
        return response()->json([
            'accessToken' => $token, // Nome bate com o LoginResponse do Android
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // =======================================================
    // MÉTODO 'register' QUE FALTAVA
    // =======================================================
    /**
     * Lida com a tentativa de registro da API
     */
    public function register(Request $request)
    {
        // 1. Validação (bate com os campos do seu app)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        // 2. Criação do Usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Criptografa a senha
        ]);

        // 3. Cria o token de acesso para o novo usuário
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Retorna a mesma resposta do Login (para logar o usuário automaticamente)
        return response()->json([
            'accessToken' => $token, // Nome bate com o LoginResponse do Android
            'token_type' => 'Bearer',
            'user' => $user
        ], 201); // 201 = Created
    }
}