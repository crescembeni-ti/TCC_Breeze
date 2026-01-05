<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro;
use App\Models\Topico;
use App\Models\User;
use App\Models\Analyst;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceOrder;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ContactController extends Controller
{
    /* ============================================================
     * FORMULÁRIO DO SITE
     * ============================================================ */
    public function index()
    {
        $bairros = Bairro::orderBy('nome', 'asc')->get();
        $topicos = Topico::orderBy('nome', 'asc')->get();
        return view('pages.contact', compact('bairros', 'topicos'));
    }

    /* ============================================================
     * SALVAR SOLICITAÇÃO (NASCE SEM ANALISTA)
     * ============================================================ */
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

        $statusEmAnalise = Status::where('name', 'Em Análise')->firstOrFail();

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
            'status_id' => $statusEmAnalise->id,
            'analyst_id' => null,
            'service_id' => null,
            'justificativa' => null,
        ]);

        return redirect()->route('contact')
            ->with('success', 'Sua solicitação foi enviada com sucesso!');
    }

    /* ============================================================
     * LISTAGEM DO ADMIN (VÊ TUDO)
     * ============================================================ */
    public function adminContactList(Request $request)
    {
        $filtro = $request->query('filtro', 'todas');
        $pendentes = ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'];
        $resolvidas = ['Concluído', 'Indeferido', 'Sem Pendências'];

        $query = Contact::with(['status', 'user']);

        if ($filtro === 'pendentes') {
            $query->whereHas('status', fn($q) => $q->whereIn('name', $pendentes));
        } elseif ($filtro === 'resolvidas') {
            $query->whereHas('status', fn($q) => $q->whereIn('name', $resolvidas));
        }

        $messages = $query->latest()->get();
        $allStatuses = Status::where('name', '!=', 'Cancelado')->get();

        $analistas = Analyst::orderBy('name')->get();
        $servicos = Service::orderBy('name')->get();

        return view('admin.contacts.index', compact('messages', 'allStatuses', 'filtro', 'analistas', 'servicos'));
    }

    /* ============================================================
     * ENCaminhar (ADMIN) → DEFINE ANALYST + SERVICE + STATUS DEFERIDO
     * ============================================================ */
   /* ============================================================
     * ENCAMINHAR (ADMIN)
     * ============================================================ */
    public function forward(Request $request, Contact $contact)
    {
        // 1. Validação: Agora aceita nulo (nullable)
        // Assim, se você mandar só serviço, não dá erro no analista.
        $validated = $request->validate([
            'analyst_id' => 'nullable|exists:analysts,id',
            'service_id' => 'nullable|exists:services,id',
        ]);

        // 2. Prepara os dados para salvar
        // Usamos array_filter para remover campos vazios/nulos se quiser manter o anterior,
        // mas aqui vamos salvar o que vier.
        $dataToUpdate = [];
        
        if ($request->has('analyst_id')) {
            $dataToUpdate['analyst_id'] = $request->analyst_id;
        }
        
        if ($request->has('service_id')) {
            $dataToUpdate['service_id'] = $request->service_id;
        }

        // 3. Atualiza o Contato
        $contact->update($dataToUpdate);

        // 4. Lógica de Status
        // Se encaminhou para Analista -> Status "Deferido"
        // Se encaminhou para Serviço -> Status "Vistoriado" (ou outro que preferir)
        if ($request->filled('analyst_id')) {
            $status = Status::where('name', 'Deferido')->first();
            if($status) $contact->update(['status_id' => $status->id]);
        } 
        elseif ($request->filled('service_id')) {
            $status = Status::where('name', 'Vistoriado')->first(); // Exemplo
            if($status) $contact->update(['status_id' => $status->id]);
        }

        return response()->json([
            'message' => 'Solicitação encaminhada com sucesso!',
            'contact' => $contact->fresh(['status', 'user']), // Recarrega com status novo
        ]);
    }

    /* ============================================================
     * UPDATE DE STATUS (ADMIN)
     * ============================================================ */
    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $statusCancelado = Status::where('name', 'Cancelado')->first()->id ?? null;
        $statusIndeferidoId = Status::where('name', 'Indeferido')->first()->id;

        if ($request->status_id == $statusCancelado) {
            return back()->withErrors(['error' => 'Administrador não pode cancelar solicitações.']);
        }

        $validated = $request->validate([
            'status_id' => 'required|exists:statuses,id',
            'justificativa' => [Rule::requiredIf($request->status_id == $statusIndeferidoId)],
        ]);

        $contact->update([
            'status_id' => $validated['status_id'],
            'justificativa' => $validated['justificativa'] ?? null
        ]);

        // Notificação Firebase
        try {
            $user = $contact->user;
            if ($user && $user->fcm_token) {
                $messaging = app('firebase.messaging');
                $notification = Notification::create(
                    'Sua solicitação foi atualizada!',
                    'O status agora é: ' . $contact->status->name
                );
                $message = CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification($notification);

                $messaging->send($message);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação FCM: ' . $e->getMessage());
        }

        return back()->with('success', 'Status atualizado.');
    }

    /* ============================================================
     * ADMIN → LISTA E MOSTRA ORDENS
     * ============================================================ */
    public function adminServiceOrders()
    {
        $oss = ServiceOrder::with(['contact.user', 'contact.status'])->latest()->get();
        return view('admin.os.index', compact('oss'));
    }

    public function adminServiceOrderShow($id)
    {
        $os = ServiceOrder::with('contact.user', 'contact.status')->findOrFail($id);
        return view('admin.os.show', compact('os'));
    }

    /* ============================================================
     * USUÁRIO → MINHAS SOLICITAÇÕES
     * ============================================================ */
    public function userRequestList()
    {
        $statusCanceladoId = Status::where('name', 'Cancelado')->first()->id;
        $myRequests = Auth::user()->contacts()
            ->with('status')
            ->where('status_id', '!=', $statusCanceladoId)
            ->latest()->get();

        return view('pages.my-requests', compact('myRequests'));
    }

    public function cancelRequest(Request $request, Contact $contact)
    {
        if (Auth::id() !== $contact->user_id) abort(403);

        $statusEmAnaliseId = Status::where('name', 'Em Análise')->first()->id;
        $statusDeferidoId = Status::where('name', 'Deferido')->first()->id;
        $statusCanceladoId = Status::where('name', 'Cancelado')->first()->id;

        if (!in_array($contact->status_id, [$statusEmAnaliseId, $statusDeferidoId])) {
            return back()->withErrors(['cancel_error' => 'Esta solicitação não pode mais ser cancelada.']);
        }

        $request->validate(['justificativa_cancelamento' => 'nullable|string|max:500']);

        $contact->update([
            'status_id' => $statusCanceladoId,
            'justificativa' => $request->justificativa_cancelamento
                ? 'Cancelado pelo usuário: ' . $request->justificativa_cancelamento
                : 'Cancelado pelo usuário.'
        ]);

        return redirect()->route('contact.myrequests')->with('success', 'Solicitação cancelada.');
    }

    /* ============================================================
     * ANALISTA → DASHBOARD
     * ============================================================ */
    public function analystDashboard()
    {
        $analystId = Auth::guard('analyst')->id() ?? 0;

        $statusPendentes = ['Deferido', 'Em Execução'];
        $statusConcluidos = ['Concluído', 'Vistoriado', 'Indeferido', 'Sem Pendências'];

        $countPendentes = Contact::where('analyst_id', $analystId)
            ->whereHas('status', fn($q) => $q->whereIn('name', $statusPendentes))
            ->count();

        $countConcluidas = Contact::where('analyst_id', $analystId)
            ->whereHas('status', fn($q) => $q->whereIn('name', $statusConcluidos))
            ->count();

        $vistorias = Contact::with(['status', 'user'])
            ->where('analyst_id', $analystId)
            ->whereHas('status', fn($q) => $q->whereIn('name', $statusPendentes))
            ->latest()
            ->take(5)
            ->get();

        return view('analista.dashboard', compact('vistorias', 'countPendentes', 'countConcluidas'));
    }

    /* ============================================================
     * ANALISTA → LISTA DE VISTORIAS
     * ============================================================ */
    public function vistoriasPendentes()
    {
        $analystId = Auth::guard('analyst')->id() ?? 0;

        $statusPermitidos = ['Deferido', 'Em Execução'];

        $vistorias = Contact::with(['status', 'user'])
            ->where('analyst_id', $analystId)
            ->whereHas('status', fn($q) => $q->whereIn('name', $statusPermitidos))
            ->latest()
            ->get();

        return view('analista.vistorias-pendentes', compact('vistorias'));
    }

    /* ============================================================
     * ANALISTA → GERAR ORDEM DE SERVIÇO
     * ============================================================ */
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
            'observacoes' => 'nullable|string',
            'especies' => 'nullable|string|max:255',
            'quantidade' => 'nullable|integer',
            'lat_long_lat' => 'nullable|string|max:50', // Assumindo renomeado na view
            'lat_long_lon' => 'nullable|string|max:50', // Assumindo renomeado na view
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
            'especies' => $request->especies,
            'quantidade' => $request->quantidade,
            'latitude' => $request->lat_long_lat, // Salvando Latitude
            'longitude' => $request->lat_long_lon, // Salvando Longitude
        ]);

        // Mantém o status como "Deferido"
        $statusDeferido = Status::where('name', 'Deferido')->first();
        Contact::find($request->contact_id)->update(['status_id' => $statusDeferido->id]);

        return redirect()->route('analyst.vistorias.pendentes')
            ->with('success', 'Ordem de Serviço gerada com sucesso!');
    }
}
