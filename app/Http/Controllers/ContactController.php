<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro;
use App\Models\Topico;
use App\Models\Analyst;
use App\Models\Service;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ContactController extends Controller
{
    /* ============================================================
     * FORMULÁRIO – USUÁRIO
     * ============================================================ */
    public function index()
    {
        return view('pages.contact', [
            'bairros' => Bairro::orderBy('nome')->get(),
            'topicos' => Topico::orderBy('nome')->get(),
        ]);
    }

    /* ============================================================
     * USUÁRIO → CRIA SOLICITAÇÃO
     * Status inicial: Em Análise
     * ============================================================ */
    public function store(Request $request)
    {
        $request->validate([
            'topico' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'nullable|string|max:20',
            'descricao' => 'required|string',
            'fotos' => 'nullable|array|max:3',
            'fotos.*' => 'image|max:2048',
        ]);

        $paths = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $paths[] = $foto->store('solicitacoes', 'public');
            }
        }

        Contact::create([
            'topico' => $request->topico,
            'bairro' => $request->bairro,
            'rua' => $request->rua,
            'numero' => $request->numero,
            'descricao' => $request->descricao,
            'fotos' => $paths,
            'user_id' => Auth::id(),
            'nome_solicitante' => Auth::user()->name,
            'email_solicitante' => Auth::user()->email,
            'status_id' => Status::where('name', 'Em Análise')->first()->id,
        ]);

        return redirect()->route('contact')->with('success', 'Solicitação enviada.');
    }

    /* ============================================================
     * ADMIN → LISTA DE SOLICITAÇÕES
     * ============================================================ */
    public function adminContactList(Request $request)
    {
        $filtro = $request->get('filtro', 'pendentes');

        $query = Contact::with('status', 'user');

        if ($filtro === 'pendentes') {
            $query->whereHas('status', fn ($q) =>
                $q->whereIn('name', [
                    'Em Análise',
                    'Deferido',
                    'Vistoriado',
                    'Em Execução'
                ])
            );
        }

        if ($filtro === 'resolvidas') {
            $query->whereHas('status', fn ($q) =>
                $q->whereIn('name', [
                    'Concluído',
                    'Indeferido',
                    'Sem Pendências'
                ])
            );
        }

        return view('admin.contacts.index', [
            'messages' => $query->latest()->get(),
            'allStatuses' => Status::where('name', '!=', 'Cancelado')->get(),
            'analistas' => Analyst::orderBy('name')->get(),
            'servicos' => Service::orderBy('name')->get(),
            'filtro' => $filtro,
        ]);
    }

    /* ============================================================
     * ADMIN → ENVIA PARA ANALISTA
     * (NÃO altera status, só cria OS)
     * ============================================================ */
    public function sendToAnalyst(Request $request, Contact $contact)
    {
        $request->validate([
            'analyst_id' => 'required|exists:analysts,id',
        ]);

        // Só associa analista
        $contact->update([
            'analyst_id' => $request->analyst_id,
        ]);

        // Cria ou reaproveita OS
        ServiceOrder::firstOrCreate(
            ['contact_id' => $contact->id],
            [
                'analyst_id' => $request->analyst_id,
                'flow' => 'analista',
            ]
        );

        return back()->with('success', 'Solicitação enviada ao analista.');
    }

    /* ============================================================
     * ADMIN → ENVIA OS PARA SERVIÇO
     * ============================================================ */
    public function sendToService(Request $request, ServiceOrder $os)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $os->update([
            'service_id' => $request->service_id,
            'flow' => 'servico',
        ]);

        $os->contact->update([
            'status_id' => Status::where('name', 'Em Execução')->first()->id,
        ]);

        return back()->with('success', 'Enviado para equipe de serviço.');
    }

    /* ============================================================
     * ADMIN → ATUALIZA STATUS MANUAL (ÚNICO LUGAR QUE DEFERE)
     * ============================================================ */
    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $indeferido = Status::where('name', 'Indeferido')->first()->id;

        $request->validate([
            'status_id' => 'required|exists:statuses,id',
            'justificativa' => Rule::requiredIf($request->status_id == $indeferido),
        ]);

        $contact->update($request->only('status_id', 'justificativa'));

        // 🔔 Notificação usuário
        try {
            if ($contact->user?->fcm_token) {
                app('firebase.messaging')->send(
                    CloudMessage::withTarget('token', $contact->user->fcm_token)
                        ->withNotification(
                            Notification::create(
                                'Solicitação atualizada',
                                'Novo status: ' . $contact->status->name
                            )
                        )
                );
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return back()->with('success', 'Status atualizado.');
    }

    /* ============================================================
     * USUÁRIO → MINHAS SOLICITAÇÕES
     * ============================================================ */
    public function userRequestList()
    {
        return view('pages.my-requests', [
            'myRequests' => Auth::user()
                ->contacts()
                ->with('status')
                ->whereHas('status', fn ($q) => $q->where('name', '!=', 'Cancelado'))
                ->latest()
                ->get(),
        ]);
    }

    /* ============================================================
     * ANALISTA → FINALIZA VISTORIA
     * ============================================================ */
    public function storeServiceOrder(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'data_vistoria' => 'required|date',
        ]);

        $os = ServiceOrder::where('contact_id', $request->contact_id)->firstOrFail();

        $os->update([
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
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'flow' => 'finalizada',
        ]);

        $os->contact->update([
            'status_id' => Status::where('name', 'Vistoriado')->first()->id,
        ]);

        return redirect()->route('analyst.dashboard')
            ->with('success', 'OS devolvida ao administrador.');
    }
}
