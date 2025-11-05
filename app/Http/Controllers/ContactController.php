<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status; // 1. IMPORTAR O NOVO MODELO
use Illuminate\Support\Facades\Auth; // Facade para autenticação
use Illuminate\Support\Facades\Cache; // 2. IMPORTAR O CACHE
use Illuminate\Validation\Rule; // 3. IMPORTAR REGRAS DE VALIDAÇÃO

class ContactController extends Controller
{
    /**
     * Mostra o formulário de contato.
     */
    public function index()
    {
        return view('pages.contact');
    }

    /**
     * Salva a solicitação de contato.
     * (Adaptado para usar status_id e "Em Análise" como padrão)
     */
    public function store(Request $request)
    {
        $user = Auth::user(); 

        $validated = $request->validate([
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'descricao' => 'required|string',
        ]);
        
        // 4. Busca o ID do status "Em Análise"
        // (Usa o Cache para não consultar o BD toda hora)
        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            // firstOrFail() vai falhar se você não tiver rodado o Seeder
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });

        // 5. Combina os dados para salvar
        $dataToSave = array_merge($validated, [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name, 
            'email_solicitante' => $user->email,
            'status_id' => $statusEmAnaliseId, // <-- CORRIGIDO
            'justificativa' => null, // Garante que comece nulo
        ]);

        // 6. Salva no banco de dados
        Contact::create($dataToSave);

        return redirect()->route('contact')->with('success', 'Sua solicitação foi enviada com sucesso! Ela já está "Em Análise".');
    }

    // --- MÉTODOS DE ADMIN (ADAPTADOS) ---

    /**
     * [ADMIN] Mostra a lista de contatos para o admin.
     * (Adaptado para carregar relações e todos os status)
     */
    public function adminContactList()
    {
        // 7. Carrega as solicitações com o nome do Status e do Usuário
        $messages = Contact::with('status', 'user') 
                           ->latest()
                           ->get(); 

        // 8. Pega TODOS os status do BD para preencher a "caixinha"
        $allStatuses = Status::all();

        return view('admin.contacts.index', compact('messages', 'allStatuses'));
    }

    /**
     * [ADMIN] Atualiza o status de uma solicitação.
     * (Adaptado para validar status_id e justificativa)
     */
    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        // 9. Busca o ID do status "Indeferido" (para a validação condicional)
        $statusIndeferidoId = Cache::remember('status_indeferido_id', 3600, function () {
            return Status::where('name', 'Indeferido')->firstOrFail()->id;
        });

        // 10. Valida os dados do formulário do admin
        $validated = $request->validate([
            // Garante que o ID do status existe na tabela 'statuses'
            'status_id' => 'required|integer|exists:statuses,id',
            
            // A justificativa é obrigatória APENAS SE o status for "Indeferido"
            'justificativa' => [
                'nullable',
                'string',
                Rule::requiredIf($request->status_id == $statusIndeferidoId)
            ],
        ]);

        // 11. Prepara os dados para salvar
        $dataToSave = [
            'status_id' => $validated['status_id'],
            'justificativa' => $validated['justificativa'],
        ];

        // 12. Se o status for mudado para algo que NÃO é "Indeferido",
        //     limpa a justificativa (caso exista de uma rejeição anterior)
        if ($validated['status_id'] != $statusIndeferidoId) {
            $dataToSave['justificativa'] = null;
        }

        $contact->update($dataToSave);

        return redirect()->route('admin.contacts.index')->with('success', 'Status da mensagem atualizado.');
    }

    // --- MÉTODOS DO USUÁRIO ---

    /**
     * [USUÁRIO] Mostra a lista de solicitações do usuário logado.
     */
    public function userRequestList()
    {
        // 13. Pega os contatos do usuário logado, já carregando o nome do status
        $myRequests = Auth::user()
                        ->contacts()
                        ->with('status') // <-- Carrega o nome do status
                        ->latest()
                        ->get();

        return view('pages.my-requests', compact('myRequests'));
    }

    /**
     * [NOVO MÉTODO - ADAPTADO]
     * Permite que um usuário cancele a própria solicitação.
     */
    public function cancelRequest(Request $request, Contact $contact)
    {
        // 1. Verificação de Segurança: O usuário logado é o dono desta solicitação?
        if (Auth::id() !== $contact->user_id) {
            abort(403); // Proibido
        }

        // 2. Busca os IDs dos status (usando cache para otimizar)
        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });
        
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            // Certifique-se de ter rodado o seeder para "Cancelado"
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        // 3. Regra de Negócio: Só pode cancelar se estiver "Em Análise"
        if ($contact->status_id !== $statusEmAnaliseId) {
            return back()->withErrors(['cancel_error' => 'Esta solicitação não pode mais ser cancelada.']);
        }

        // 4. Valida o motivo (opcional)
        $request->validate([
            'justificativa_cancelamento' => 'nullable|string|max:500'
        ]);

        // 5. Atualiza a solicitação
        $contact->update([
            'status_id' => $statusCanceladoId,
            // Reutilizamos o campo 'justificativa' para o motivo do cancelamento
            'justificativa' => $request->justificativa_cancelamento 
                                ? 'Cancelado pelo usuário: ' . $request->justificativa_cancelamento
                                : 'Cancelado pelo usuário (sem motivo informado).'
        ]);

        return redirect()->route('contact.myrequests')->with('success', 'Solicitação cancelada com sucesso.');
    }
}