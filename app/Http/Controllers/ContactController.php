<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro;
use App\Models\Topico;
use App\Models\User;
use App\Models\Analyst; // <--- IMPORTANTE: Model da tabela analysts
use App\Models\Service; // <--- IMPORTANTE: Model da tabela services
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceOrder;
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

        $validated = $request->validate([
            'topico' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'nullable|string|max:10',
            'descricao' => 'required|string',
            'fotos' => 'nullable|array|max:3',
            'fotos.*' => 'image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $caminhosFotos = [];

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $caminhosFotos[] = $foto->store('solicitacoes', 'public');
            }
        }

        $statusEmAnaliseId = Status::where('name', 'Em Análise')->firstOrFail()->id;

        Contact::create([
            'topico' => $validated['topico'],
            'bairro' => $validated['bairro'],
            'rua' => $validated['rua'],
            'numero' => $validated['numero'] ?? null,
            'descricao' => $validated['descricao'],
            'fotos' => $caminhosFotos,
            'user_id' => $user->id,
            'nome_solicitante' => $user->name,
            'email_solicitante' => $user->email,
            'status_id' => $statusEmAnaliseId,
            'justificativa' => null,
            'analyst_id' => null,
            'service_id' => null,
        ]);

        return redirect()->route('contact')
            ->with('success', 'Sua solicitação foi enviada com sucesso!');
    }

    /**
     * [SITE ADMIN] Lista de solicitações com filtros.
     * ADAPTADO: Busca Analistas e Serviços em suas respectivas tabelas.
     */
    public function adminContactList(Request $request)
    {
        // 1. Filtros de Status
        $filtro = $request->query('filtro', 'todas');
        $pendentes = ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'];
        $resolvidas = ['Concluído', 'Indeferido', 'Sem Pendências'];

        // Carrega relacionamentos. Se você tiver 'analyst' e 'service' no model Contact, adicione no array.
        $query = Contact::with(['status', 'user']); 

        if ($filtro === 'pendentes') {
            $query->whereHas('status', fn($q) => $q->whereIn('name', $pendentes));
        } elseif ($filtro === 'resolvidas') {
            $query->whereHas('status', fn($q) => $q->whereIn('name', $resolvidas));
        }

        $messages = $query->latest()->get();
        $allStatuses = Status::where('name', '!=', 'Cancelado')->get();

        // 2. BUSCAR DADOS DAS TABELAS SEPARADAS
        
        // Tabela: analysts
        try {
            $analistas = Analyst::orderBy('name', 'asc')->get();
        } catch (\Exception $e) {
            $analistas = []; // Evita crash se a tabela não existir
        }

        // Tabela: services
        try {
            $servicos = Service::orderBy('name', 'asc')->get();
        } catch (\Exception $e) {
            $servicos = []; // Evita crash se a tabela não existir
        }

        return view('admin.contacts.index', compact('messages', 'allStatuses', 'filtro', 'analistas', 'servicos'));
    }

    /**
     * [NOVO] Método para Encaminhar Solicitação
     * Valida IDs nas tabelas 'analysts' e 'services'
     */
    public function forward(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'analyst_id' => 'nullable|exists:analysts,id', // Valida na tabela analysts
            'service_id' => 'nullable|exists:services,id', // Valida na tabela services
        ]);

        $contact->update([
            'analyst_id' => $request->input('analyst_id'),
            'service_id' => $request->input('service_id'),
        ]);

        $contact->load(['status', 'user']); 

        return response()->json([
            'message' => 'Solicitação encaminhada com sucesso!',
            'contact' => $contact,
        ]);
    }

    /**
     * [SITE ADMIN] Atualiza status e envia notificação.
     */
    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $statusCancelado = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->first()->id ?? null;
        });

        $statusIndeferidoId = Cache::remember('status_indeferido_id', 3600, function () {
            return Status::where('name', 'Indeferido')->first()->id;
        });

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

        if ($validated['status_id'] != $statusIndeferidoId) {
            $dataToSave['justificativa'] = null;
        }

        $contact->update($dataToSave);
        $contact->load('status', 'user');

        // Notificação Firebase
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

        if ($request->wantsJson() || $request->isJson()) {
            return response()->json([
                'message' => 'Status atualizado com sucesso!',
                'contact' => $contact,
            ]);
        }

        return back()->with('success', 'Status da mensagem atualizado.');
    }

    public function adminServiceOrders()
    {
        $oss = ServiceOrder::with(['contact.user', 'contact.status'])
            ->latest()
            ->get();
        return view('admin.os.index', compact('oss'));
    }

    public function adminServiceOrderShow($id)
    {
        $os = ServiceOrder::with('contact.user', 'contact.status')->findOrFail($id);
        return view('admin.os.show', compact('os'));
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
        if (Auth::id() !== $contact->user_id) {
            abort(403);
        }

        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, fn() => Status::where('name', 'Em Análise')->firstOrFail()->id);
        $statusDeferidoId = Cache::remember('status_deferido_id', 3600, fn() => Status::where('name', 'Deferido')->firstOrFail()->id);
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, fn() => Status::where('name', 'Cancelado')->firstOrFail()->id);

        if (!in_array($contact->status_id, [$statusEmAnaliseId, $statusDeferidoId])) {
            return back()->withErrors([
                'cancel_error' => 'Esta solicitação não pode mais ser cancelada.'
            ]);
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

    /**
     * [1] DASHBOARD (Analista)
     */
    public function analystDashboard()
    {
        $statusPendentes = ['Deferido', 'Em Análise', 'Em Execução'];
        $statusConcluidos = ['Concluído', 'Vistoriado', 'Indeferido', 'Sem Pendências'];

        $countPendentes = Contact::whereHas('status', fn($q) => $q->whereIn('name', $statusPendentes))->count();
        $countConcluidas = Contact::whereHas('status', fn($q) => $q->whereIn('name', $statusConcluidos))->count();

        $vistorias = Contact::with(['status', 'user'])
            ->whereHas('status', fn($q) => $q->whereIn('name', $statusPendentes))
            ->latest()
            ->take(5)
            ->get();

        return view('analista.dashboard', compact('vistorias', 'countPendentes', 'countConcluidas'));
    }

    /**
     * [2] LISTA COMPLETA (Página de 'Gerar OS')
     */
    public function vistoriasPendentes()
    {
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
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'data_vistoria' => 'required|date',
            'data_execucao' => 'nullable|date',
            'motivo' => 'nullable|array',
            'servico' => 'nullable|array',
            'equip' => 'nullable|array',
            'procedimentos' => 'nullable|array',
            'observacoes' => 'nullable|string'
        ]);

        ServiceOrder::create([
            'contact_id' => $request->contact_id,
            'supervisor_id' => Auth::guard('analyst')->id(),
            'data_vistoria' => $request->data_vistoria,
            'data_execucao' => $request->data_execucao,
            'motivos' => $request->motivo,
            'servicos' => $request->servico,
            'equipamentos' => $request->equip,
            'procedimentos' => $request->procedimentos,
            'observacoes' => $request->observacoes,
        ]);

        $contact = Contact::find($request->contact_id);
        $statusDeferido = Status::where('name', 'Deferido')->first();

        if ($statusDeferido) {
            $contact->update(['status_id' => $statusDeferido->id]);
        }

        return redirect()->route('analyst.vistorias.pendentes')
            ->with('success', 'Ordem de Serviço gerada com sucesso!');
    }
}