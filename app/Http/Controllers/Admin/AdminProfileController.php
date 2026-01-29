<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador responsável pela gestão do perfil pessoal do Administrador.
 * Permite que o administrador logado gerencie suas próprias credenciais, 
 * como alteração de senha e encerramento de conta.
 */
class AdminProfileController extends Controller
{
    /**
     * Exibe o formulário de edição de perfil para o administrador autenticado.
     */
    public function edit(Request $request)
    {
        $admin = $request->user('admin');
        return view('admin.profile.edit', compact('admin'));
    }

    /**
     * Processa a alteração da senha de acesso do administrador.
     * 
     * Blocos de lógica:
     * - Validação: Garante que a senha atual seja informada e que a nova senha tenha 
     *   no mínimo 8 caracteres e confirmação idêntica.
     * - Verificação de Segurança: Utiliza Hash::check para validar se a senha atual 
     *   fornecida coincide com a armazenada (criptografada) no banco de dados.
     * - Persistência: Aplica a nova criptografia e salva o registro.
     */
    public function updatePassword(Request $request)
    {
        $admin = $request->user('admin');

        // Validação rigorosa dos campos de senha
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.required' => 'Informe sua senha atual.',
            'password.min' => 'A nova senha deve conter pelo menos 8 caracteres.',
            'password.confirmed' => 'As senhas não conferem.',
        ]);

        // Impede a troca se a senha atual estiver errada
        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors([
                'current_password' => 'A senha atual está incorreta.',
            ]);
        }

        // Atualização segura da credencial
        $admin->password = Hash::make($request->password);
        $admin->save();

        return back()->with('success', 'Senha alterada com sucesso!');
    }

    /**
     * Remove a conta do administrador logado permanentemente.
     * 
     * Blocos de lógica:
     * - Exclusão: Remove o registro do banco de dados.
     * - Logout: Invalida a sessão administrativa atual.
     * - Redirecionamento: Retorna o usuário para a página inicial pública.
     */
    public function destroy(Request $request)
    {
        $admin = $request->user('admin');
        $admin->delete();

        // Encerra a sessão no guard específico de admin
        auth('admin')->logout();

        return redirect('/')->with('success', 'Conta de administrador excluída com sucesso.');
    }
}
