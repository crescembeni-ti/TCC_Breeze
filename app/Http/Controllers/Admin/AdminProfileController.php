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

    public function update(Request $request)
    {
        $admin = $request->user('admin');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function destroy(Request $request)
    {
        $admin = $request->user('admin');
        $admin->delete();

        auth('admin')->logout();

        return redirect('/')->with('success', 'Conta de administrador excluída com sucesso.');
    }
}
