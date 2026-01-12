<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Analyst;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Necessário para a validação de update

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
        // 1. Descobre qual a tabela no banco de dados baseada no tipo
        // Isso é crucial para validar se o email/cpf já existe NAQUELA tabela específica
        $tableName = match ($request->type) {
            'admin'   => (new Admin)->getTable(),   // Geralmente 'admins'
            'analyst' => (new Analyst)->getTable(), // Geralmente 'analysts'
            'service' => (new Service)->getTable(), // Geralmente 'services'
            default   => 'admins',
        };

        // 2. Regras de validação
        $rules = [
            'type'     => 'required',
            'name'     => 'required|string|max:255',
            // Valida se o email é único na tabela descoberta acima
            'email'    => "required|email|unique:$tableName,email",
            'password' => 'required|min:6|confirmed',
        ];

        // 3. Adiciona validação de CPF apenas se NÃO for admin
        if ($request->type !== 'admin') {
            // Verifica se o CPF é único na tabela correta
            $rules['cpf'] = "required|string|unique:$tableName,cpf";
        }

        // 4. Executa a validação com mensagens personalizadas
        $request->validate($rules, [
            'email.unique' => 'Este e-mail já está em uso.',
            'cpf.unique'   => 'Este CPF já está cadastrado no sistema.',
            'password.confirmed' => 'As senhas não coincidem.',
        ]);

        // 5. Criação do registro
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
        // 1. Busca o modelo primeiro para pegar a tabela e o registro atual
        $modelClass = $this->getModel($type);
        $model = $modelClass::findOrFail($id);
        $tableName = $model->getTable();

        // 2. Regras básicas
        $rules = [
            'name' => 'required|string|max:255',
            // Validação de Unique IGNORANDO o ID atual (Rule::unique)
            // Isso permite salvar o próprio usuário sem dar erro de duplicidade
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
                // Valida único ignorando o ID atual
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

    public function destroy($type, $id)
    {
        // Adicionei uma proteção simples para Admin não se auto-deletar (opcional)
        if ($type === 'admin' && auth()->id() == $id && auth()->user() instanceof Admin) {
             return back()->with('error', 'Você não pode excluir sua própria conta.');
        }

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