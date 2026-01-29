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
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

/**
 * Controlador de API para gestão de solicitações (Contatos).
 * Este controlador é o principal ponto de interação entre o Aplicativo Android e o Servidor.
 * Além de gerenciar os dados, ele integra-se com o Firebase (FCM) para enviar notificações 
 * push em tempo real para o celular do cidadão.
 */
class ContatoApiController extends Controller
{
    /**
     * Cria uma nova solicitação enviada via aplicativo.
     * 
     * Blocos de lógica:
     * - Validação: Checa campos de endereço, título e imagem.
     * - Upload: Salva a foto enviada pelo celular no storage público.
     * - Resposta: Retorna o objeto JSON da solicitação criada (essencial para o app atualizar a lista local).
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
        $statusEmAnaliseId = $statusEmAnalise ? $statusEmAnalise->id : 1; 

        $contact = Contact::create([
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
        ]); 

        return response()->json([
            'message' => 'Solicitação criada com sucesso!',
            'data' => $contact 
        ], 201);
    }

    /**
     * Lista as solicitações do usuário logado na API.
     * Filtra solicitações canceladas para limpar a visão do usuário no app.
     */
    public function userRequestListApi(Request $request)
    {
        $user = $request->user();
        $statusCancelado = Status::where('name', 'Cancelado')->first();
        
        $query = $user->contacts()->with('status')->latest();
        
        if ($statusCancelado) {
            $query->where('status_id', '!=', $statusCancelado->id);
        }
        
        return response()->json($query->get());
    }
    
    /**
     * Lista todas as solicitações (Visão Administrativa na API).
     */
    public function adminRequestListApi(Request $request)
    {
        $solicitacoes = Contact::with('status', 'user')->latest()->get();
        return response()->json($solicitacoes);
    }

    /**
     * Retorna a lista de funcionários com perfil de Analista.
     * Utilizado no app administrativo para designar responsáveis.
     */
    public function getAnalystsList()
    {
        $analysts = User::where('role', 'analista')->select('id', 'name', 'email')->get();
        return response()->json($analysts);
    }

    /**
     * Atualiza o status de uma solicitação via API e dispara Notificação Push.
     * 
     * Blocos de lógica:
     * - Cache: Otimiza a busca do ID de status 'Indeferido'.
     * - Validação: Exige justificativa apenas se o status for 'Indeferido'.
     * - Integração Firebase: Se o usuário tiver um fcm_token salvo, envia uma notificação 
     *   personalizada com os dados da atualização para o celular.
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
            'designated_to' => 'nullable|integer|exists:users,id'
        ]);

        $dataToSave = [
            'status_id' => $validated['status_id'],
            'justificativa' => $validated['justificativa'] ?? null,
        ];

        if ($validated['status_id'] != $statusIndeferidoId) {
            $dataToSave['justificativa'] = null;
        }
        
        if ($request->has('designated_to')) {
            $dataToSave['designated_to'] = $validated['designated_to'];
        }

        $contact->update($dataToSave);
        
        // Envio de Notificação Push via Firebase Cloud Messaging (FCM)
        try {
            $contact->load('status', 'user', 'responsible'); 
            $user = $contact->user; 
            $fcmToken = $user->fcm_token; 

            if ($fcmToken) {
                $messaging = app('firebase.messaging');
                $responsavelNome = $contact->responsible ? $contact->responsible->name : 'Ninguém';
                
                $notification = Notification::create(
                    'Sua solicitação foi atualizada!', 
                    'Status: ' . $contact->status->name . '. Responsável: ' . $responsavelNome
                );

                // Constrói a mensagem com dados extras para o app abrir na tela correta
                $message = CloudMessage::withTarget('token', $fcmToken)
                    ->withNotification($notification)
                    ->withData([
                        'click_action' => 'OPEN_SOLICITACAO_DETALHES', 
                        'solicitacao_id' => (string)$contact->id, 
                        'EXTRA_ADMIN_TITULO' => $contact->topico,
                        'EXTRA_ADMIN_STATUS' => $contact->status->name,
                        'EXTRA_ADMIN_IMAGE_URI' => $contact->foto_path ? Storage::url($contact->foto_path) : '',
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
     * Filtra solicitações por grupos de status (Abas) na API.
     * Agrupa status como 'pendentes', 'andamento' ou 'finalizadas' para facilitar a navegação no app.
     */
    public function adminRequestListByStatusApi(Request $request, $statusName)
    {
        $statusNomes = match ($statusName) {
            'pendentes' => ['Em Análise'],
            'andamento' => ['Deferido', 'Vistoriado', 'Em Execução'],
            'finalizadas' => ['Concluído', 'Sem Pendências', 'Indeferido', 'Cancelado'],
            default => [$statusName],
        };

        $statusIds = Status::whereIn('name', $statusNomes)->pluck('id');

        $solicitacoes = Contact::whereIn('status_id', $statusIds)
                            ->with('status', 'user')
                            ->latest()
                            ->get();

        return response()->json($solicitacoes);
    }
}
