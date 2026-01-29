<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;
use App\Mail\PasswordResetCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Controlador de Autenticação da API.
 * Responsável por fornecer acesso seguro ao Aplicativo Móvel (Android/iOS).
 * Gerencia Tokens de Acesso (Sanctum), Registro de Usuários, Recuperação de Senha 
 * via e-mail e Gestão de Perfil (Foto e Dados).
 */
class AuthController extends Controller
{
    /**
     * Autentica o usuário e gera um Token de Acesso (Bearer Token).
     * 
     * Blocos de lógica:
     * - Validação: Checa se e-mail e senha foram enviados.
     * - Verificação Manual: Busca o usuário e valida o Hash da senha (evita bugs de sessão web).
     * - Emissão de Token: Utiliza o Laravel Sanctum para criar uma "chave" persistente para o app.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Busca o usuário para validação manual de credenciais
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais estão incorretas.'],
            ]);
        }
        
        // Gera o token que será enviado no cabeçalho Authorization do app
        $token = $user->createToken('auth_token_do_app')->plainTextToken;

        return response()->json([
            'accessToken' => $token, 
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Registra um novo cidadão através do aplicativo.
     * 
     * Blocos de lógica:
     * - Unicidade: Garante que o e-mail não esteja cadastrado.
     * - Segurança: Criptografa a senha antes de salvar.
     * - Auto-Login: Já retorna o token de acesso após o cadastro para fluidez do app.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'accessToken' => $token, 
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Invalida o token atual, realizando o logout do dispositivo.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    /**
     * Gerencia o upload e substituição da foto de perfil via API.
     * 
     * Blocos de lógica:
     * - Limpeza: Apaga o arquivo físico antigo do storage para economizar espaço.
     * - Armazenamento: Salva a nova imagem na pasta 'avatars' dentro do disco público.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();

        // Remove a foto anterior do servidor
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Salva o novo arquivo
        $path = $request->file('photo')->store('avatars', 'public');

        $user->update([
            'profile_photo_path' => $path,
        ]);

        return response()->json([
            'message' => 'Foto atualizada com sucesso',
            'path' => Storage::url($path)
        ]);
    }

    /**
     * Inicia o processo de recuperação de senha enviando um código de 6 dígitos.
     * 
     * Blocos de lógica:
     * - Geração: Cria um código numérico aleatório.
     * - Expiração: Define uma janela de 10 minutos para o uso do código.
     * - Fila (Queue): Envia o e-mail em segundo plano para não travar a resposta do app.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();
        $code = random_int(100000, 999999);

        // Salva o código temporário no perfil do usuário
        $user->update([
            'email_verification_code' => $code, 
            'email_verification_code_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        try {
            // Utiliza queue para performance
            Mail::to($user->email)->queue(new PasswordResetCodeMail($code));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao enviar e-mail. Verifique sua configuração.'], 500);
        }

        return response()->json(['message' => 'Código de redefinição enviado para seu e-mail.']);
    }

    /**
     * Valida o código recebido por e-mail e define a nova senha.
     * 
     * Blocos de lógica:
     * - Verificação de Tempo: Checa se o código ainda é válido (não expirou).
     * - Transação: Garante que a troca de senha e a limpeza do código ocorram juntas.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string|min:6|max:6',
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        $user = User::where('email', $request->email)->first();

        // Valida integridade do código e tempo de expiração
        if (!$user || 
            !$user->email_verification_code_expires_at ||
            Carbon::now()->isAfter($user->email_verification_code_expires_at) ||
            $request->code != $user->email_verification_code) 
        {
            return response()->json(['message' => 'Código inválido ou expirado.'], 401);
        }

        // Atualiza a senha e limpa os campos temporários
        DB::transaction(function () use ($user, $request) {
            $user->update([
                'password' => Hash::make($request->password),
                'email_verification_code' => null,
                'email_verification_code_expires_at' => null
            ]);
        });

        return response()->json(['message' => 'Senha redefinida com sucesso.']);
    }

    /**
     * Salva o Token do Firebase (FCM) para permitir o envio de notificações push para o celular.
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user(); 
        $user->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json(['message' => 'Token FCM salvo com sucesso.']);
    }

    /**
     * Atualiza os dados básicos do perfil (Nome) e permite a troca de senha 
     * validando a senha atual para segurança extra.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'nullable|required_with:password|string',
            'password' => ['nullable', 'confirmed', Rules\Password::min(6)],
        ]);

        // Se houver tentativa de mudar a senha, exige a confirmação da atual
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'A senha atual está incorreta.',
                    'errors' => ['current_password' => ['Senha incorreta']]
                ], 422);
            }
            
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->name;
        $user->save();

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'user' => $user
        ]);
    }

    /**
     * Exclui permanentemente a conta do usuário solicitada via app.
     * Remove fotos e limpa registros vinculados (cascata).
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->delete();

        return response()->json(['message' => 'Conta excluída com sucesso.']);
    }
}
