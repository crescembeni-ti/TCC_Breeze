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

// --- IMPORTS ADICIONADOS PARA REDEFINIÇÃO DE SENHA ---
use App\Mail\PasswordResetCodeMail; // O E-mail que criamos
use Illuminate\Support\Facades\Mail; // Para enviar o e-mail
use Illuminate\Support\Facades\DB;   // Para a transação
use Carbon\Carbon; // Para o tempo de expiração

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
    public unction register(Request $request)
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

    /**
     * NOVO MÉTODO 1: Pedir o código
     */
    public function forgotPassword(Request $request)
    {
        // 1. Valida o e-mail
        $request->validate(['email' => 'required|email|exists:users,email']);

        // 2. Encontra o usuário
        $user = User::where('email', $request->email)->first();

        // 3. Gera um código de 6 dígitos
        $code = random_int(100000, 999999);

        // 4. Salva o código (hashed) e a expiração no banco
        $user->update([
            'email_verification_code' => Hash::make($code),
            'email_verification_code_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        // 5. Envia o e-mail
        try {
            Mail::to($user->email)->send(new PasswordResetCodeMail($code));
        } catch (\Exception $e) {
            // Se o envio de e-mail falhar
            return response()->json(['message' => 'Erro ao enviar e-mail. Verifique sua configuração.'], 500);
        }

        return response()->json(['message' => 'Código de redefinição enviado para seu e-mail.']);
    }

    /**
     * NOVO MÉTODO 2: Redefinir a senha
     */
    public function resetPassword(Request $request)
    {
        // 1. Valida todos os campos
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|min:6|max:6',
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        // 2. Encontra o usuário
        $user = User::where('email', $request->email)->first();

        // 3. Verifica o código e a expiração
        if (!$user || 
            !$user->email_verification_code_expires_at ||
            Carbon::now()->isAfter($user->email_verification_code_expires_at) ||
            !Hash::check($request->code, $user->email_verification_code)) 
        {
            return response()->json(['message' => 'Código inválido ou expirado.'], 401);
        }

        // 4. Se tudo estiver OK, atualiza a senha
        DB::transaction(function () use ($user, $request) {
            $user->update([
                'password' => Hash::make($request->password),
                'email_verification_code' => null, // Limpa o código
                'email_verification_code_expires_at' => null // Limpa a expiração
            ]);
        });

        return response()->json(['message' => 'Senha redefinida com sucesso.']);
    }

    // =======================================================
    //  MÉTODO ADICIONADO (Para salvar o token FCM)
    // =======================================================
    /**
     * Salva o token FCM (Firebase) do dispositivo do usuário.
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user(); // Pega o usuário logado
        $user->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json(['message' => 'Token FCM salvo com sucesso.']);
    }
}