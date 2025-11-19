<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro; 
use App\Models\Topico; 
use App\Models\User;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Log;

// Imports do Firebase
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ContatoApiController extends Controller
{
    /**
     * [API] Salva a solicitação de contato vinda do App.
     * (Substitui o seu método 'store' simples)
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

        // Lógica de Status (Em Análise)
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
            'status_id' => $statusEmAnaliseId, // Usamos ID, não string 'novo'
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
     * [API ADMIN] Retorna TODAS as solicitações (Backup).
     */
    public function adminRequestListApi(Request $request)
    {
        $solicitacoes = Contact::with('status', 'user')->latest()->get();
        return response()->json($solicitacoes);
    }

    /**
     * [API ADMIN] Atualiza o status e envia notificação Push.
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
                        'EXTRA_ADMIN_ID' => (string)$contact->id,
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
     * [API ADMIN] Retorna solicitações filtradas por GRUPO (Abas).
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