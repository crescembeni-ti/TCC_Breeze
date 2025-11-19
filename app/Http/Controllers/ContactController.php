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

// Imports do Firebase
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log; 

class ContactController extends Controller
{
    public function index()
    {
        $bairros = Bairro::orderBy('nome', 'asc')->get();
        $topicos = Topico::orderBy('nome', 'asc')->get();
        return view('pages.contact', compact('bairros', 'topicos'));
    }

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
        $statusEmAnaliseId = $statusEmAnalise ? $statusEmAnalise->id : 1; 

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
    
    public function adminRequestListApi(Request $request)
    {
        $solicitacoes = Contact::with('status', 'user') 
                            ->latest()
                            ->get();
        return response()->json($solicitacoes);
    }

    // ===================================================================
    //  MÉTODO 'adminUpdateStatusApi' (ATUALIZADO E CORRIGIDO)
    // ===================================================================
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
        
        // --- ENVIAR NOTIFICAÇÃO PUSH ---
        try {
            $contact->load('status', 'user'); 
            $user = $contact->user; 
            $fcmToken = $user->fcm_token; 

            if ($fcmToken) {
                $messaging = app('firebase.messaging');
                
                $notification = Notification::create(
                    'Sua solicitação foi atualizada!', 
                    'O status da sua solicitação "' . $contact->topico . '" agora é: ' . $contact->status->name
                );

                $message = CloudMessage::withTarget('token', $fcmToken)
                    ->withNotification($notification)
                    ->withData([
                        'click_action' => 'OPEN_SOLICITACAO_DETALHES', 
                        'solicitacao_id' => (string)$contact->id, 
                        'EXTRA_ADMIN_ID' => (string)$contact->id, // Enviando o ID
                        'EXTRA_ADMIN_TITULO' => $contact->topico,
                        'EXTRA_ADMIN_DATA' => $contact->created_at->toIso8601String(),
                        'EXTRA_ADMIN_STATUS' => $contact->status->name,
                        'EXTRA_ADMIN_USUARIO' => $contact->user->name,
                        'EXTRA_ADMIN_IMAGE_URI' => $contact->foto_path ? Storage::url($contact->foto_path) : '',
                        'EXTRA_ADMIN_DESCRICAO' => $contact->descricao ?? '',
                        'EXTRA_ADMIN_BAIRRO' => $contact->bairro,
                        'EXTRA_ADMIN_RUA' => $contact->rua,
                        'EXTRA_ADMIN_NUMERO' => $contact->numero
                    ]);

                $messaging->send($message);
            }

        } catch (\Exception $e) {
            Log::error('Falha ao enviar notificação FCM: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Status atualizado com sucesso!',
            'data' => $contact
        ]);
    }
    
    /**
     * [API ADMIN] Retorna solicitações filtradas por GRUPO de status.
     */
    public function adminRequestListByStatusApi(Request $request, $statusName)
    {
        $statusNomes = [];

        switch ($statusName) {
            case 'pendentes':
                $statusNomes = ['Em Análise'];
                break;
            case 'andamento':
                $statusNomes = ['Deferido', 'Vistoriado', 'Em Execução'];
                break;
            case 'finalizadas':
                $statusNomes = ['Concluído', 'Sem Pendências', 'Indeferido', 'Cancelado'];
                break;
            default:
                $statusNomes = [$statusName];
                break;
        }

        $statusIds = Status::whereIn('name', $statusNomes)->pluck('id');

        $solicitacoes = Contact::whereIn('status_id', $statusIds)
                            ->with('status', 'user')
                            ->latest()
                            ->get();

        return response()->json($solicitacoes);
    }
}