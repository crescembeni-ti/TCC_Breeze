<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminProfileController extends Controller
{
    public function edit(Request $request)
    {
        $admin = $request->user('admin');
        return view('admin.profile.edit', compact('admin'));
    }


public function updatePassword(Request $request)
    {
    $admin = $request->user('admin');

    // =========================
    // VALIDAÇÃO
    // =========================
    $request->validate([
        'current_password' => ['required'],
        'password' => ['required', 'confirmed', 'min:8'],
    ], [
        'current_password.required' => 'Informe sua senha atual.',
        'password.min' => 'A nova senha deve conter pelo menos 8 caracteres.',
        'password.confirmed' => 'As senhas não conferem.',
    ]);

    // =========================
    // CONFERE SENHA ATUAL
    // =========================
    if (!Hash::check($request->current_password, $admin->password)) {
        return back()->withErrors([
            'current_password' => 'A senha atual está incorreta.',
        ]);
    }

    // =========================
    // ATUALIZA SENHA
    // =========================
    $admin->password = Hash::make($request->password);
    $admin->save();

    return back()->with('success', 'Senha alterada com sucesso!');
    }


    public function destroy(Request $request)
    {
        $admin = $request->user('admin');
        $admin->delete();

        auth('admin')->logout();

        return redirect('/')->with('success', 'Conta de administrador excluída com sucesso.');
    }
}
