<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro; // Sua importação de Bairro
use App\Models\Topico; // Sua importação de Tópico
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Sua importação de Storage

class ContactController extends Controller
{
    /**
     * Mostra o formulário de contato e carrega bairros e tópicos.
     * (SEU MÉTODO ESTÁ CORRETO)
     */
    public function index()
    {
        $bairros = Bairro::orderBy('nome', 'asc')->get();
        $topicos = Topico::orderBy('nome', 'asc')->get();
        return view('pages.contact', compact('bairros', 'topicos'));
    }

    /**
     * Salva a solicitação de contato.
     * (SEU MÉTODO ESTÁ CORRETO)
     */
    public function store(Request $request)
    {
        $user = Auth::user(); 
        
        $nomesDeBairrosValidos = Bairro::pluck('nome')->toArray();
        $nomesDeTopicosValidos = Topico::pluck('nome')->toArray(); 

        $validated = $request->validate([
            'topico' => [ 
                'required', 'string', 'max:255',
                Rule::in($nomesDeTopicosValidos) 
            ],
            'bairro' => [
                'required', 'string', 'max:255',
                Rule::in($nomesDeBairrosValidos) 
            ],
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'descricao' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);
        
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('solicitacoes', 'public');
        }
        
        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });

        $dataToSave = array_merge($validated, [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name, 
            'email_solicitante' => $user->email,
            'status_id' => $statusEmAnaliseId,
            'justificativa' => null, 
            'foto_path' => $fotoPath,
        ]);

        Contact::create($dataToSave);

        return redirect()->route('contact')->with('success', 'Sua solicitação foi enviada com sucesso! Ela já está "Em Análise".');
    }

    // --- MÉTODOS DE ADMIN (ESTÃO CORRETOS) ---

    public function adminContactList()
    {
        $messages = Contact::with('status', 'user') 
                           ->latest()
                           ->get(); 
        $allStatuses = Status::all();
        return view('admin.contacts.index', compact('messages', 'allStatuses'));
    }

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


    // --- MÉTODOS DO USUÁRIO ---

    /**
     * [USUÁRIO] Mostra a lista de solicitações do usuário logado.
     * (ADAPTADO para "desaparecer" com os cancelados)
     */
    public function userRequestList()
    {
        // 1. Busca o ID do status "Cancelado"
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            // Certifique-se de ter rodado o Seeder com o status "Cancelado"
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        // 2. Busca as solicitações do usuário
        $myRequests = Auth::user()
                        ->contacts()
                        ->with('status') // Carrega o nome do status
                        
                        // --- ESTA É A SUA LÓGICA DE "DESAPARECER" ---
                        // Onde o status_id NÃO SEJA o ID de "Cancelado"
                        ->where('status_id', '!=', $statusCanceladoId) 
                        // --- FIM DA LÓGICA ---

                        ->latest()
                        ->get();

        return view('pages.my-requests', compact('myRequests'));
    }

    /**
     * [USUÁRIO] Permite que um usuário cancele a própria solicitação.
     * (ADAPTADO para a sua regra: pode cancelar TUDO, exceto "Concluído")
     */
    public function cancelRequest(Request $request, Contact $contact)
    {
        // 1. Verificação de Segurança (Dono)
        if (Auth::id() !== $contact->user_id) {
            abort(403); 
        }

        // 2. Busca os IDs dos status que BLOQUEIAM o cancelamento
        $statusConcluidoId = Cache::remember('status_concluido_id', 3600, function () {
            return Status::where('name', 'Concluído')->firstOrFail()->id;
        });
        
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        // 3. [NOVA REGRA] Se o status for "Concluído" OU "Cancelado", bloqueia.
        if ($contact->status_id === $statusConcluidoId || $contact->status_id === $statusCanceladoId) {
            return back()->withErrors(['cancel_error' => 'Esta solicitação não pode mais ser cancelada.']);
        }

        // 4. Valida o motivo (opcional)
        $request->validate([
            'justificativa_cancelamento' => 'nullable|string|max:500'
        ]);

        // 5. Atualiza a solicitação para "Cancelado"
        $contact->update([
            'status_id' => $statusCanceladoId,
            'justificativa' => $request->justificativa_cancelamento 
                                ? 'Cancelado pelo usuário: ' . $request->justificativa_cancelamento
                                : 'Cancelado pelo usuário (sem motivo informado).'
        ]);

        // Redireciona de volta. A página vai recarregar, a mensagem de sucesso
        // vai aparecer, e o item vai "desaparecer" (graças à lógica no userRequestList).
        return redirect()->route('contact.myrequests')->with('success', 'Solicitação cancelada com sucesso.');
    }
}