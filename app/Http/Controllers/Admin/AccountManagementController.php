<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Analyst;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
        // 1. Descobre a tabela correta para validação unique
        $tableName = match ($request->type) {
            'admin'   => (new Admin)->getTable(),
            'analyst' => (new Analyst)->getTable(),
            'service' => (new Service)->getTable(),
            default   => 'admins',
        };

        // 2. Regras de validação
        $rules = [
            'type'     => 'required',
            'name'     => 'required|string|max:255',
            'email'    => "required|email|unique:$tableName,email",
            'password' => 'required|min:6|confirmed',
        ];

        // 3. Adiciona validação de CPF apenas se NÃO for admin
        if ($request->type !== 'admin') {
            $rules['cpf'] = "required|string|unique:$tableName,cpf";
        }

        // 4. Executa a validação
        $request->validate($rules, [
            'email.unique' => 'Este e-mail já está em uso.',
            'cpf.unique'   => 'Este CPF já está cadastrado no sistema.',
            'password.confirmed' => 'As senhas não coincidem.',
        ]);

        // 5. Criação do registro
        match ($request->type) {
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
        // 1. Busca o modelo e a tabela
        $modelClass = $this->getModel($type);
        $model = $modelClass::findOrFail($id);
        $tableName = $model->getTable();

        // 2. Regras básicas
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 
                'email', 
                Rule::unique($tableName)->ignore($id)
            ],
        ];

        // 3. Validação de senha (opcional no update)
        if ($request->filled('password')) {
            $rules['password'] = 'confirmed|min:6';
        }

        // 4. Validação de CPF (se não for admin)
        if ($type !== 'admin') {
            $rules['cpf'] = [
                'required', 
                'string', 
                Rule::unique($tableName)->ignore($id)
            ];
        }

        // 5. Executa validação
        $request->validate($rules, [
            'email.unique' => 'Este e-mail já pertence a outro usuário.',
            'cpf.unique'   => 'Este CPF já pertence a outro usuário.',
        ]);

        // 6. Atualização dos dados
        $model->name = $request->name;
        $model->email = $request->email;

        if ($type !== 'admin') {
            $model->cpf = $request->cpf;
        }

        if ($request->filled('password')) {
            $model->password = Hash::make($request->password);
        }

        $model->save();

        return back()->with('success', 'Conta atualizada com sucesso!');
    }

    public function destroy(Request $request, $type, $id)
    {
        // 1. Busca o registro alvo
        $modelClass = $this->getModel($type);
        $model = $modelClass::findOrFail($id);

        // 2. Proteção contra auto-exclusão (evita trancar a si mesmo fora)
        if ($type === 'admin' && auth()->id() == $id && auth()->user() instanceof Admin) {
             return back()->with('error', 'Você não pode excluir sua própria conta por aqui.');
        }

        // 3. VERIFICAÇÃO DE SENHA (SÓ PARA ADMIN)
        if ($type === 'admin') {
            $request->validate([
                'password' => 'required|string',
            ], [
                'password.required' => 'É necessário informar a senha do administrador alvo para confirmar.',
            ]);

            // Verifica se a senha digitada bate com a do USUÁRIO ALVO ($model->password)
            if (!Hash::check($request->password, $model->password)) {
                return back()->with('error', 'Senha incorreta. Você deve informar a senha do administrador que está sendo excluído.');
            }
        }

        // 4. Exclusão
        $model->delete();

        return back()->with('success', 'Conta excluída com sucesso!');
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