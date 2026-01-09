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

class ContactController extends Controller
{
    // ==========================================
    // ÁREA DO CIDADÃO
    // ==========================================
    public function index()
    {
        return view('pages.contact', [
            'bairros' => Bairro::orderBy('nome')->get(),
            'topicos' => Topico::orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'topico' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
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

    // ==========================================
    // ÁREA DO ADMIN (CORRIGIDA: destino em vez de flow)
    // ==========================================
    public function adminContactList(Request $request)
    {
        $filtro = $request->get('filtro', 'pendentes');

        // Carrega serviceOrder para o botão "Ver OS"
        $query = Contact::with(['status', 'user', 'serviceOrder']);

        if ($filtro === 'pendentes') {
            $query->whereHas('status', fn ($q) =>
                $q->whereIn('name', ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'])
            );

            // CORREÇÃO: Esconde se 'destino' for 'analista' ou 'servico'
            // Isso garante que saia da lista de solicitações enquanto viaja
            $query->whereDoesntHave('serviceOrder', function ($q) {
                $q->whereIn('destino', ['analista', 'servico']);
            });
        }

        if ($filtro === 'resolvidas') {
            $query->whereHas('status', fn ($q) =>
                $q->whereIn('name', ['Concluído', 'Indeferido', 'Sem Pendências'])
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

    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $indeferido = Status::where('name', 'Indeferido')->first()->id;
        $request->validate([
            'status_id' => 'required|exists:statuses,id',
            'justificativa' => Rule::requiredIf($request->status_id == $indeferido),
        ]);
        $contact->update($request->only('status_id', 'justificativa'));
        return back()->with('success', 'Status atualizado.');
    }

    // ==========================================
    // ENCAMINHAMENTO (ADMIN -> ANALISTA/SERVIÇO)
    // ==========================================
    public function forward(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        if ($request->has('analyst_id')) return $this->sendToAnalyst($request, $contact);
        if ($request->has('service_id')) {
            $os = ServiceOrder::firstOrCreate(['contact_id' => $contact->id]);
            return $this->sendToService($request, $os);
        }

        return response()->json(['message' => 'Nenhum destino selecionado.'], 400);
    }

    public function sendToAnalyst(Request $request, Contact $contact)
    {
        $request->validate(['analyst_id' => 'required|exists:analysts,id']);
        
        $contact->update(['analyst_id' => $request->analyst_id]);
        
        // Define destino='analista' -> Some da lista principal e vai para OS
        ServiceOrder::updateOrCreate(
            ['contact_id' => $contact->id],
            [
                'analyst_id' => $request->analyst_id,
                'destino' => 'analista', // CORRIGIDO (era flow)
                'status' => 'enviada',
                'service_id' => null 
            ]
        );

        return response()->json(['success' => true, 'message' => 'Enviado para o analista!']);
    }

    public function sendToService(Request $request, ServiceOrder $os)
    {
        $request->validate(['service_id' => 'required|exists:services,id']);
        
        // Define destino='servico' -> Some da lista principal
        $os->update([
            'service_id' => $request->service_id,
            'destino' => 'servico', // CORRIGIDO (era flow)
            'status' => 'pendente_aceite' 
        ]);

        return response()->json(['success' => true, 'message' => 'Enviado para equipe de serviço.']);
    }

    // ==========================================
    // ANALISTA SALVA VISTORIA
    // ==========================================
   public function storeServiceOrder(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $os = ServiceOrder::where('contact_id', $request->contact_id)->firstOrFail();

        // 1. Atualiza OS e LIMPA O DESTINO (sai da lista de OS)
        $os->update([
            'laudo_tecnico' => $request->laudo_tecnico ?? $os->laudo_tecnico,
            'motivos' => $request->motivo ?? null,
            'servicos' => $request->servico ?? null,
            'equipamentos' => $request->equip ?? null,
            'observacoes' => $request->observacoes ?? null,
            'supervisor_id' => Auth::guard('analyst')->id(),
            'data_vistoria' => now(),
            
            'destino' => null, // <--- ISSO TIRA DA LISTA DE OS
            'status' => 'analise_concluida'
        ]);

        // 2. Muda Status para VISTORIADO (Entra na lista de Vistoriados)
        $statusVistoriado = Status::where('name', 'vistoriado')->first();
        
        if ($statusVistoriado) {
            $os->contact->update(['status_id' => $statusVistoriado->id]);
        }

        return redirect()->route('analyst.dashboard')
            ->with('success', 'Vistoria concluída! Enviado para Vistoriado.');
    }
  }
