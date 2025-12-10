<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Analyst;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountManagementController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'admin');

        $data = match ($type) {
            'admin'   => Admin::paginate(10),
            'analyst' => Analyst::paginate(10),
            'service' => Service::paginate(10),
            default   => Admin::paginate(10),
        };

        return view('admin.accounts.index', compact('data', 'type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'     => 'required',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'cpf'      => 'nullable|string',
            'password' => 'required|min:6|confirmed',   // SENHA OBRIGATÓRIA + CONFIRMAÇÃO
        ]);

        $type = $request->type;

        match ($type) {
            'admin' => Admin::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]),

            'analyst' => Analyst::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'cpf'      => $request->cpf,
                'password' => Hash::make($request->password),
            ]),

            'service' => Service::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'cpf'      => $request->cpf,
                'password' => Hash::make($request->password),
            ]),
        };

        return back()->with('success', 'Conta criada com sucesso!');
    }

    public function update(Request $request, $type, $id)
    {
        $rules = [
            'name'  => 'required',
            'email' => 'required|email',
        ];

        // Validação da senha SOMENTE SE o usuário tentar alterar
        if ($request->filled('password')) {
            $rules['password'] = 'confirmed|min:6'; // senha tem que bater com password_confirmation
        }

        $request->validate($rules);

        $model = $this->getModel($type)::findOrFail($id);

        $model->name = $request->name;
        $model->email = $request->email;

        if ($type !== 'admin') {
            $model->cpf = $request->cpf;
        }

        // Atualiza senha somente se for enviada
        if ($request->filled('password')) {
            $model->password = Hash::make($request->password);
        }

        $model->save();

        return back()->with('success', 'Conta atualizada com sucesso!');
    }

    public function destroy($type, $id)
    {
        $model = $this->getModel($type)::findOrFail($id);
        $model->delete();

        return back()->with('success', 'Conta excluída!');
    }

    private function getModel($type)
    {
        return match ($type) {
            'admin'   => Admin::class,
            'analyst' => Analyst::class,
            'service' => Service::class,
        };
    }
}
