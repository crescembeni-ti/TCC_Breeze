<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro; // Importação está correta
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule; // Importação está correta

class ContactController extends Controller
{
    /**
     * Mostra o formulário de contato e carrega os bairros.
     * (ESTE MÉTODO ESTÁ CORRETO)
     */
    public function index()
    {
        // 2. BUSCA OS BAIRROS DO BANCO
        $bairros = Bairro::orderBy('nome', 'asc')->get();

        // 3. ENVIA A VARIÁVEL $bairros PARA A VIEW
        return view('pages.contact', compact('bairros'));
    }

    /**
     * Salva a solicitação de contato.
     * (AJUSTADO PARA VALIDAR O NOME DO BAIRRO)
     */
    public function store(Request $request)
    {
        $user = Auth::user(); 

        // --- INÍCIO DA ATUALIZAÇÃO ---
        
        // 1. Pega os NOMES válidos da tabela 'bairros'
        $nomesDeBairrosValidos = Bairro::pluck('nome')->toArray();

        // 2. VALIDAÇÃO ATUALIZADA PARA 'bairro' (TEXTO)
        $validated = $request->validate([
            'bairro' => [
                'required',
                'string',
                'max:255',
                Rule::in($nomesDeBairrosValidos) // Garante que o nome exista na tabela bairros
            ],
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'descricao' => 'required|string',
        ]);
        
        // --- FIM DA ATUALIZAÇÃO ---

        // 5. O CÓDIGO ABAIXO ESTAVA FORA DO MÉTODO, CAUSANDO O ParseError
        
        // Busca o ID do status "Em Análise"
        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });

        // Combina os dados para salvar
        // $validated agora contém 'bairro' (o nome)
        $dataToSave = array_merge($validated, [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name, 
            'email_solicitante' => $user->email,
            'status_id' => $statusEmAnaliseId,
            'justificativa' => null, 
        ]);

        // Salva no banco de dados
        Contact::create($dataToSave);

        return redirect()->route('contact')->with('success', 'Sua solicitação foi enviada com sucesso! Ela já está "Em Análise".');
    }

    // --- MÉTODOS DE ADMIN (Sem alterações) ---

    /**
     * [ADMIN] Mostra a lista de contatos para o admin.
     */
    public function adminContactList()
    {
        $messages = Contact::with('status', 'user') 
                            ->latest()
                            ->get(); 

        $allStatuses = Status::all();

        return view('admin.contacts.index', compact('messages', 'allStatuses'));
    }

    /**
     * [ADMIN] Atualiza o status de uma solicitação.
     */
    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $statusIndeferidoId = Cache::remember('status_indeferido_id', 3600, function () {
            return Status::where('name', 'Indeferido')->firstOrFail()->id;
        });

        $validated = $request->validate([
            'status_id' => 'required|integer|exists:statuses,id',
            'justificativa' => [
                'nullable',
                'string',
                Rule::requiredIf($request->status_id == $statusIndeferidoId)
            ],
        ]);

        $dataToSave = [
            'status_id' => $validated['status_id'],
            'justificativa' => $validated['justificativa'],
        ];

        if ($validated['status_id'] != $statusIndeferidoId) {
            $dataToSave['justificativa'] = null;
        }

        $contact->update($dataToSave);

        return redirect()->route('admin.contacts.index')->with('success', 'Status da mensagem atualizado.');
    }

    // --- MÉTODOS DO USUÁRIO (Sem alterações) ---

    /**
     * [USUÁRIO] Mostra a lista de solicitações do usuário logado.
     */
    public function userRequestList()
    {
        $myRequests = Auth::user()
                            ->contacts()
                            ->with('status') 
                            ->latest()
                            ->get();

        return view('pages.my-requests', compact('myRequests'));
    }

    /**
     * [USUÁRIO] Permite que um usuário cancele a própria solicitação.
     */
    public function cancelRequest(Request $request, Contact $contact)
    {
        if (Auth::id() !== $contact->user_id) {
            abort(403);
        }

        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });
        
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        if ($contact->status_id !== $statusEmAnaliseId) {
            return back()->withErrors(['cancel_error' => 'Esta solicitação não pode mais ser cancelada.']);
        }

        $request->validate([
            'justificativa_cancelamento' => 'nullable|string|max:500'
        ]);

        $contact->update([
            'status_id' => $statusCanceladoId,
            'justificativa' => $request->justificativa_cancelamento 
                                    ? 'Cancelado pelo usuário: ' . $request->justificativa_cancelamento
                                    : 'Cancelado pelo usuário (sem motivo informado).'
        ]);

        return redirect()->route('contact.myrequests')->with('success', 'Solicitação cancelada com sucesso.');
    }
}