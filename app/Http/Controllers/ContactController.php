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
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
<<<<<<< HEAD
use App\Models\User; // (Já deve existir, mas garanta)
use Illuminate\Support\Facades\Log; // <-- ADICIONADO PARA LOG DE ERRO
=======
use App\Models\User;
use Illuminate\Support\Facades\Log;
>>>>>>> aa3aac746533f187b6a75d4481e4a4150e11ea34

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

    /**
     * PÁGINA ADMIN: lista com filtro e agrupamento
     */
    public function adminContactList(Request $request)
    {
        // filtro: todas | pendentes | resolvidas
        $filtro = $request->query('filtro', 'todas');

        // grupos de status (nomes)
        $pendentes = ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'];
        $resolvidas = ['Concluído', 'Indeferido', 'Sem Pendências'];

        if ($filtro === 'pendentes') {
            $messages = Contact::with('status', 'user')
                ->whereHas('status', fn($q) => $q->whereIn('name', $pendentes))
                ->latest()
                ->get();
        } elseif ($filtro === 'resolvidas') {
            $messages = Contact::with('status', 'user')
                ->whereHas('status', fn($q) => $q->whereIn('name', $resolvidas))
                ->latest()
                ->get();
        } else {
            $messages = Contact::with('status', 'user')->latest()->get();
        }

        // pego todos os statuses exceto 'Cancelado' (admin não pode cancelar)
        $allStatuses = Status::where('name', '!=', 'Cancelado')->get();

        return view('admin.contacts.index', compact('messages', 'allStatuses', 'filtro'));
    }

    /**
     * Atualiza status (rota usada pelo admin).
     * Aceita requisição AJAX (retorna JSON) ou normal (redirect).
     */
    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $statusCancelado = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->first()->id ?? null;
        });

        $statusIndeferidoId = Cache::remember('status_indeferido_id', 3600, function () {
            return Status::where('name', 'Indeferido')->first()->id;
        });

        // NÃO PERMITIR admin setar 'Cancelado'
        if ($request->status_id == $statusCancelado) {
            return $request->wantsJson()
                ? response()->json(['error' => 'Administrador não pode cancelar solicitações.'], 403)
                : back()->withErrors(['error' => 'Administrador não pode cancelar solicitações.']);
        }

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
            'justificativa' => $validated['justificativa'] ?? null,
        ];

        // só mantem justificativa quando indeferido
        if ($validated['status_id'] != $statusIndeferidoId) {
            $dataToSave['justificativa'] = null;
        }

        $contact->update($dataToSave);
        $contact->load('status', 'user');

        // try enviar push (se tiver FCM token) — não quebra se falhar
        try {
            $user = $contact->user;
            $fcmToken = $user->fcm_token ?? null;
            if ($fcmToken) {
                $messaging = app('firebase.messaging');
                $notification = Notification::create(
                    'Sua solicitação foi atualizada!',
                    'O status da sua solicitação "' . ($contact->topico ?? 'solicitação') . '" agora é: ' . $contact->status->name
                );
                $message = CloudMessage::withTarget('token', $fcmToken)
                    ->withNotification($notification);

                $messaging->send($message);
            }
        } catch (\Exception $e) {
            Log::error('Falha ao enviar notificação FCM: ' . $e->getMessage());
        }

        // resposta JSON para AJAX
        if ($request->wantsJson() || $request->isJson()) {
            return response()->json([
                'message' => 'Status atualizado com sucesso!',
                'contact' => $contact,
            ]);
        }

        return back()->with('success', 'Status da mensagem atualizado.');
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
    // garantir que só o dono pode cancelar
    if (Auth::id() !== $contact->user_id) {
        abort(403);
    }

    // buscar IDs dos status
    $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, fn() =>
        Status::where('name', 'Em Análise')->firstOrFail()->id
    );

    $statusDeferidoId = Cache::remember('status_deferido_id', 3600, fn() =>
        Status::where('name', 'Deferido')->firstOrFail()->id
    );

    $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, fn() =>
        Status::where('name', 'Cancelado')->firstOrFail()->id
    );

    // VERIFICA SE O STATUS ATUAL PERMITE CANCELAMENTO
    if (!in_array($contact->status_id, [$statusEmAnaliseId, $statusDeferidoId])) {
        return back()->withErrors([
            'cancel_error' => 'Esta solicitação não pode mais ser cancelada.'
        ]);
    }

    // validar motivo opcional
    $request->validate([
        'justificativa_cancelamento' => 'nullable|string|max:500'
    ]);

    // realizar cancelamento
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
    //  MÉTODO 'adminUpdateStatusApi' ATUALIZADO COM "DEEP LINKING"
    // ===================================================================
    /**
     * [API ADMIN] Atualiza o status de uma solicitação e envia notificação.
     */
    public function adminUpdateStatusApi(Request $request, Contact $contact)
    {
        // 1. Busca o ID do status "Indeferido" (para a validação)
        $statusIndeferidoId = Cache::remember('status_indeferido_id', 3600, function () {
            return Status::where('name', 'Indeferido')->firstOrFail()->id;
        });

        // 2. Valida os dados (status_id é obrigatório, justificativa é opcional)
        $validated = $request->validate([
            'status_id' => 'required|integer|exists:statuses,id',
            'justificativa' => [
                'nullable',
                'string',
                Rule::requiredIf($request->status_id == $statusIndeferidoId)
            ],
        ]);

        // 3. Prepara os dados para salvar
        $dataToSave = [
            'status_id' => $validated['status_id'],
            'justificativa' => $validated['justificativa'],
        ];

        if ($validated['status_id'] != $statusIndeferidoId) {
            $dataToSave['justificativa'] = null;
        }

        // 4. Atualiza o banco
        $contact->update($dataToSave);
        
        // =======================================================
        //  5. (ATUALIZADO) ENVIAR NOTIFICAÇÃO PUSH COM DADOS
        // =======================================================
        try {
            // Recarrega o 'contact' para pegar o nome do novo status
            $contact->load('status', 'user'); 
            
            // Pega o usuário que CRIOU a solicitação
            $user = $contact->user; 
            
            // Pega o token FCM que salvamos no banco
            $fcmToken = $user->fcm_token; 

            if ($fcmToken) {
                // Prepara a notificação
                $messaging = app('firebase.messaging');
                $notification = Notification::create(
                    'Sua solicitação foi atualizada!', // Título
                    'O status da sua solicitação "' . $contact->topico . '" agora é: ' . $contact->status->name // Corpo
                );

                // Cria a mensagem para o token específico
                $message = CloudMessage::withTarget('token', $fcmToken)
                    ->withNotification($notification)
                    
                    // --- MUDANÇA AQUI: Adiciona os dados extras ---
                    ->withData([
                        'click_action' => 'OPEN_SOLICITACAO_DETALHES', // Diz ao app o que fazer
                        'solicitacao_id' => (string)$contact->id, // O ID da solicitação
                        
                        // Também enviamos os dados para a tela de detalhes
                        // (Nota: o nome do 'EXTRA' deve bater com o adapter)
                        'EXTRA_ADMIN_ID' => (string)$contact->id, // ID
                        'EXTRA_ADMIN_TITULO' => $contact->topico,
                        'EXTRA_ADMIN_DATA' => $contact->created_at->toIso8601String(), // Envia data em formato ISO
                        'EXTRA_ADMIN_STATUS' => $contact->status->name,
                        'EXTRA_ADMIN_USUARIO' => $contact->user->name,
                        'EXTRA_ADMIN_IMAGE_URI' => $contact->foto_path, // Apenas o caminho
                        'EXTRA_ADMIN_DESCRICAO' => $contact->descricao,
                        'EXTRA_ADMIN_BAIRRO' => $contact->bairro,
                        'EXTRA_ADMIN_RUA' => $contact->rua,
                        'EXTRA_ADMIN_NUMERO' => $contact->numero
                    ]);
                // --------------------------------------------------

                // Envia
                $messaging->send($message);
            }

        } catch (\Exception $e) {
            // Se o envio da notificação falhar, registra o erro
            // mas NÃO quebra a requisição. O status foi salvo com sucesso.
            Log::error('Falha ao enviar notificação FCM: ' . $e->getMessage());
        }
        // =======================================================

        // 6. Retorna uma resposta JSON
        return response()->json([
            'message' => 'Status atualizado com sucesso!',
            'data' => $contact // Retorna o contato com o novo status
        ]);
    }
    
    /**
     * [API ADMIN] Retorna solicitações filtradas por nome do status.
     */
    public function adminRequestListByStatusApi(Request $request, $statusName)
    {
        $status = Status::where('name', $statusName)->first();
        if (!$status) {
            return response()->json([]);
        }
        $solicitacoes = Contact::where('status_id', $status->id)
                            ->with('status', 'user')
                            ->latest()
                            ->get();
        return response()->json($solicitacoes);
    }
}