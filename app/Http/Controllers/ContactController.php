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
use Illuminate\Support\Facades\Log; // Mantido caso precise de logs no site
use App\Models\ServiceOrder;
// Imports do Firebase (Mantidos caso o site também envie notificações no futuro)
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ContactController extends Controller
{
    /**
     * [SITE] Página de contato (formulário).
     */
    public function index()
    {
        $bairros = Bairro::orderBy('nome', 'asc')->get();
        $topicos = Topico::orderBy('nome', 'asc')->get();
        return view('pages.contact', compact('bairros', 'topicos'));
    }

    /**
     * [SITE] Salva solicitação vinda do formulário web.
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

    /**
     * [SITE ADMIN] Lista de solicitações com filtros.
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
     * [SITE ADMIN] Atualiza status e envia notificação (se FCM existir).
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

        // Tenta enviar push (se tiver FCM token) — mas não quebra o site se falhar
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
            Log::error('Falha ao enviar notificação FCM (Site): ' . $e->getMessage());
        }

        // Se for uma requisição JSON (Ajax do site), retorna JSON
        if ($request->wantsJson() || $request->isJson()) {
            return response()->json([
                'message' => 'Status atualizado com sucesso!',
                'contact' => $contact,
            ]);
        }

        return back()->with('success', 'Status da mensagem atualizado.');
    }


    /**
     * [SITE USUÁRIO] Minhas solicitações.
     */
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

    /**
     * [SITE USUÁRIO] Cancelar solicitação.
     */
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



 // ... (outros códigos do seu controller, store, update, etc) ...

    /**
     * [1] DASHBOARD (Tela Inicial com Cards)
     * Rota: /pbi-analista/dashboard
     */
    public function analystDashboard()
    {
        $statusPendentes = ['Deferido', 'Em Análise', 'Em Execução'];
        $statusConcluidos = ['Concluído', 'Vistoriado', 'Indeferido', 'Sem Pendências'];

        // Contadores
        $countPendentes = Contact::whereHas('status', fn($q) => $q->whereIn('name', $statusPendentes))->count();
        $countConcluidas = Contact::whereHas('status', fn($q) => $q->whereIn('name', $statusConcluidos))->count();

        // Lista Resumida (5 últimas)
        $vistorias = Contact::with(['status', 'user'])
            ->whereHas('status', fn($q) => $q->whereIn('name', $statusPendentes))
            ->latest()
            ->take(5)
            ->get();

        return view('analista.dashboard', compact('vistorias', 'countPendentes', 'countConcluidas'));
    }

    /**
     * [2] LISTA COMPLETA (Página de 'Gerar OS')
     * Rota: /pbi-analista/vistorias-pendentes
     * É ESTA FUNÇÃO QUE ESTAVA FALTANDO E GERANDO O ERRO
     */
    public function vistoriasPendentes()
    {
        // Aqui pegamos todas, sem limite de quantidade
        $statusPendentes = ['Deferido', 'Em Análise', 'Em Execução'];

        $vistorias = Contact::with(['status', 'user'])
            ->whereHas('status', function ($query) use ($statusPendentes) {
                $query->whereIn('name', $statusPendentes);
            })
            ->latest()
            ->get();

        return view('analista.vistorias-pendentes', compact('vistorias'));
    }

    /**
     * [ANALISTA] Salvar a Ordem de Serviço
     */
    public function storeServiceOrder(Request $request)
    {
        // 1. Validação simples
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'data_vistoria' => 'required|date',
            'data_execucao' => 'nullable|date',
            // Arrays (checkboxes)
            'motivo' => 'nullable|array',
            'servico' => 'nullable|array',
            'equip' => 'nullable|array',
            'procedimentos' => 'nullable|array',
            'observacoes' => 'nullable|string'
        ]);

        // 2. Cria a Ordem de Serviço
        ServiceOrder::create([
            'contact_id' => $request->contact_id,
            'supervisor_id' => Auth::guard('analyst')->id(),
            'data_vistoria' => $request->data_vistoria,
            'data_execucao' => $request->data_execucao,
            
            // Salvando os arrays (o Model cuida do JSON)
            'motivos' => $request->motivo,
            'servicos' => $request->servico,
            'equipamentos' => $request->equip,
            'procedimentos' => $request->procedimentos,
            'observacoes' => $request->observacoes,
        ]);

        // 3. Atualiza o status da solicitação principal para "Deferido" (ou Em Execução)
        // Busque o ID do status que você quer. Ex: Deferido.
        $contact = Contact::find($request->contact_id);
        $statusDeferido = Status::where('name', 'Deferido')->first();
        
        if ($statusDeferido) {
            $contact->update(['status_id' => $statusDeferido->id]);
        }

        return back()->with('success', 'Ordem de Serviço gerada com sucesso!');
    }
    

} // <--- Fim da classe ContactController
