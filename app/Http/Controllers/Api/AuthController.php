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

// --- IMPORT ADICIONADO PARA A FOTO ---
use Illuminate\Support\Facades\Storage;

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
        
        // 3. Cria o Token (a "chave" de acesso)
        $token = $user->createToken('auth_token_do_app')->plainTextToken;

        // 4. Envia o token e os dados do usuário de volta para o app
        return response()->json([
            'accessToken' => $token, // Nome bate com o LoginResponse do Android
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

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

    /**
     * Lida com o logout
     */
    public function logout(Request $request)
    {
        // Revoga o token que foi usado para fazer esta requisição
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    // =======================================================
    //  MÉTODO ADICIONADO (Para o upload da foto de perfil)
    // =======================================================
    /**
     * Atualiza a foto de perfil do usuário.
     */
    public function updatePhoto(Request $request)
    {
        // 1. Valida se o arquivo 'photo' foi enviado e é uma imagem
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();

        // 2. Apaga a foto antiga, se ela existir
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // 3. Salva a nova foto (ex: em 'storage/app/public/avatars')
        // (O nome 'photo' bate com o 'MultipartBody.Part' do Android)
        $path = $request->file('photo')->store('avatars', 'public');

        // 4. Salva o caminho no banco de dados
        $user->update([
            'profile_photo_path' => $path,
        ]);

        return response()->json([
            'message' => 'Foto atualizada com sucesso',
            'path' => Storage::url($path) // Retorna a nova URL
        ]);
    }
}