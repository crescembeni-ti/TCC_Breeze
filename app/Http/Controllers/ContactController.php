<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro; 
use App\Models\Topico; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; 

class ContactController extends Controller
{
    /**
     * [MÉTODO DO SITE - INTACTO]
     */
    public function index()
    {
        $bairros = Bairro::orderBy('nome', 'asc')->get();
        $topicos = Topico::orderBy('nome', 'asc')->get();
        return view('pages.contact', compact('bairros', 'topicos'));
    }

    /**
     * [MÉTODO DO SITE - INTACTO]
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

    // --- MÉTODOS DE ADMIN (SITE) ---
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


    // --- MÉTODOS DO USUÁRIO (SITE) ---
    public function userRequestList()
    {
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        $myRequests = Auth::user()
                            ->contacts()
                            ->with('status') 
                            ->where('status_id', '!=', $statusCanceladoId) 
                            ->latest()
                            ->get();
        return view('pages.my-requests', compact('myRequests'));
    }

    public function cancelRequest(Request $request, Contact $contact)
    {
        if (Auth::id() !== $contact->user_id) {
            abort(403); 
        }

        $statusConcluidoId = Cache::remember('status_concluido_id', 3600, function () {
            return Status::where('name', 'Concluído')->firstOrFail()->id;
        });
        
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        if ($contact->status_id === $statusConcluidoId || $contact->status_id === $statusCanceladoId) {
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

    
    // ===================================================================
    // ================== MÉTODOS DA API (ANDROID) =======================
    // ===================================================================

    /**
     * [API] Salva a solicitação de contato vinda da API (Android).
     */
    public function storeApi(Request $request)
    {
        $user = $request->user(); 

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $fotoPath = null;
        if ($request->hasFile('imagem')) {
            $fotoPath = $request->file('imagem')->store('solicitacoes', 'public');
        }

        $statusEmAnalise = Status::where('name', 'Em Análise')->first();
        $statusEmAnaliseId = $statusEmAnalise ? $statusEmAnalise->id : 1; // Padrão 1 se não achar

        $dataToSave = [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name,
            'email_solicitante' => $user->email,
            'topico' => $validated['titulo'],
            'descricao' => $validated['descricao'],
            'foto_path' => $fotoPath,
            'bairro' => $validated['bairro'],
            'rua' => $validated['rua'],
            'numero' => $validated['numero'],
            'status_id' => $statusEmAnaliseId,
            'justificativa' => null,
        ];
        
        $contact = Contact::create($dataToSave); 

        return response()->json([
            'message' => 'Solicitação criada com sucesso!',
            'data' => $contact 
        ], 201);
    }

    /**
     * [API] Retorna a lista de solicitações do usuário logado.
     */
    public function userRequestListApi(Request $request)
    {
        $user = $request->user();
        $statusCancelado = Status::where('name', 'Cancelado')->first();
        $query = $user->contacts()->with('status')->latest();
        if ($statusCancelado) {
            $query->where('status_id', '!=', $statusCancelado->id);
        }
        $myRequests = $query->get();
        return response()->json($myRequests);
    }
    
    /**
     * [API ADMIN] Retorna TODAS as solicitações para o Admin (Método antigo, ok manter)
     */
    public function adminRequestListApi(Request $request)
    {
        $solicitacoes = Contact::with('status', 'user') 
                            ->latest()
                            ->get();
        return response()->json($solicitacoes);
    }

    /**
     * [API ADMIN] Atualiza o status de uma solicitação.
     */
    public function adminUpdateStatusApi(Request $request, Contact $contact)
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

        return response()->json([
            'message' => 'Status atualizado com sucesso!',
            'data' => $contact->load('status')
        ]);
    }

    // ===================================================================
    //  NOVO MÉTODO ADICIONADO (Para as Abas do Admin)
    // ===================================================================
    /**
     * [API ADMIN] Retorna solicitações filtradas por nome do status.
     */
    public function adminRequestListByStatusApi(Request $request, $statusName)
    {
        // 1. Encontra o Status pelo nome (ex: "Em Análise")
        $status = Status::where('name', $statusName)->first();

        // 2. Se o status não for encontrado, retorna uma lista vazia
        if (!$status) {
            return response()->json([]);
        }

        // 3. Busca os contatos com esse status_id
        $solicitacoes = Contact::where('status_id', $status->id)
                            ->with('status', 'user') // Carrega as relações
                            ->latest()
                            ->get();

        return response()->json($solicitacoes);
    }
}