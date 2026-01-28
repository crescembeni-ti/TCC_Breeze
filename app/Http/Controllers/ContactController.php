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
use Carbon\Carbon;

/**
 * Controlador responsável pelas solicitações de serviço (contatos).
 * Gerencia o fluxo desde o cidadão pedindo um serviço até o encaminhamento para analistas e equipes técnicas.
 */
class ContactController extends Controller
{
    /* ============================================================
     * ÁREA DO CIDADÃO (USUÁRIO COMUM)
     * ============================================================ */
    
    /**
     * Exibe o formulário onde o cidadão pode solicitar um serviço (poda, remoção, etc).
     */
    public function index()
    {
        return view('pages.contact', [
            'bairros' => Bairro::orderBy('nome')->get(),
            'topicos' => Topico::orderBy('nome')->get(),
        ]);
    }

    /**
     * Salva a solicitação do cidadão no banco de dados.
     * Inclui upload de fotos e definição do status inicial como "Em Análise".
     */
    public function store(Request $request)
    {
        // Valida os dados do formulário
        $request->validate([
            'topico' => 'required|string|max:255',
            'telefone' => 'required|string|max:20', 
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'descricao' => 'required|string',
            'fotos' => 'nullable|array|max:3',
            'fotos.*' => 'image|max:2048',
        ]);

        // Processa o upload das fotos (máximo 3)
        $paths = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $paths[] = $foto->store('solicitacoes', 'public');
            }
        }

        // Cria o registro da solicitação
        Contact::create([
            'topico' => $request->topico,
            'telefone' => $request->telefone, 
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

    /**
     * Lista as solicitações feitas pelo usuário logado.
     */
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
     * ÁREA DO ADMIN (GESTÃO)
     * ============================================================ */

    /**
     * Lista todas as solicitações para o Administrador.
     * Possui filtros por período e abas (Pendentes, Resolvidas, Todas).
     */
    public function adminContactList(Request $request)
    {
        $filtro = $request->get('filtro', 'pendentes');

        // Query base com relacionamentos necessários
        $baseQuery = Contact::with(['status', 'user', 'serviceOrder.service']);

        // Filtro de Período (7 dias, 30 dias ou data personalizada)
        if ($request->filled('period')) {
            $period = $request->period;
            if ($period == '7_days') {
                $baseQuery->where('created_at', '>=', now()->subDays(7));
            } elseif ($period == '30_days') {
                $baseQuery->where('created_at', '>=', now()->subDays(30));
            } elseif ($period == 'custom' && $request->filled('date_start') && $request->filled('date_end')) {
                $start = Carbon::parse($request->date_start)->startOfDay();
                $end = Carbon::parse($request->date_end)->endOfDay();
                $baseQuery->whereBetween('created_at', [$start, $end]);
            }
        }

        // Dados para o mapa (mostra tudo do período)
        $mapMessages = (clone $baseQuery)->get();

        // Dados para a tabela (aplica filtros das abas)
        $tableQuery = $baseQuery; 

        if ($filtro === 'pendentes') {
            $tableQuery->whereHas('status', fn ($q) =>
                $q->whereIn('name', ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'])
            );
            // Regra para mostrar apenas o que ainda não foi totalmente encaminhado
            $tableQuery->where(function ($mainQuery) {
                $mainQuery->whereDoesntHave('serviceOrder', function ($q) {
                    $q->whereIn('destino', ['analista', 'servico']);
                })
                ->orWhereHas('status', function ($q) {
                    $q->where('name', 'Em Execução');
                });
            });
        } elseif ($filtro === 'resolvidas') {
            $tableQuery->whereHas('status', fn ($q) =>
                $q->whereIn('name', ['Concluído', 'Indeferido', 'Sem Pendências'])
            );
        }

        $messages = $tableQuery->latest()->get(); 

        return view('admin.contacts.index', [
            'messages' => $messages,
            'mapMessages' => $mapMessages,
            'allStatuses' => Status::where('name', '!=', 'Cancelado')->get(),
            'analistas' => Analyst::orderBy('name')->get(),
            'servicos' => Service::orderBy('name')->get(),
            'filtro' => $filtro,
        ]);
    }

    /**
     * Atualiza o status de uma solicitação (ex: Deferir, Indeferir).
     */
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

    /* ============================================================
     * ENCAMINHAMENTO (ADMIN -> OUTROS)
     * ============================================================ */

    /**
     * Rota que decide se a solicitação vai para um Analista ou para a Equipe de Serviço.
     */
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

    /**
     * Encaminha a solicitação para um Analista realizar a vistoria técnica.
     */
    public function sendToAnalyst(Request $request, Contact $contact)
    {
        $request->validate(['analyst_id' => 'required|exists:analysts,id']);
        
        $contact->update(['analyst_id' => $request->analyst_id]);
        
        ServiceOrder::updateOrCreate(
            ['contact_id' => $contact->id],
            [
                'analyst_id' => $request->analyst_id,
                'destino' => 'analista', 
                'status' => 'enviada',
                'service_id' => null 
            ]
        );

        return response()->json(['success' => true, 'message' => 'Enviado para o analista!']);
    }

    /**
     * Encaminha a solicitação diretamente para a Equipe de Serviço (Execução).
     */
    public function sendToService(Request $request, ServiceOrder $os)
    {
        $request->validate(['service_id' => 'required|exists:services,id']);
        
        $os->update([
            'service_id' => $request->service_id,
            'destino' => 'servico',
            'status' => 'pendente_aceite' 
        ]);

        return response()->json(['success' => true, 'message' => 'Enviado para equipe de serviço.']);
    }

    /* ============================================================
     * ÁREA DO ANALISTA
     * ============================================================ */
    
    /**
     * Exibe o painel do Analista com contadores de vistorias.
     */
    public function analystDashboard()
    {
        $analystId = Auth::guard('analyst')->id();

        $countPendentes = ServiceOrder::where('analyst_id', $analystId)
            ->where('destino', 'analista')
            ->count();

        $countConcluidas = ServiceOrder::where('analyst_id', $analystId)
            ->where('status', 'analise_concluida')
            ->count();

        $vistorias = ServiceOrder::with(['contact.user']) 
            ->where('analyst_id', $analystId)
            ->where('destino', 'analista')
            ->latest()
            ->limit(5)
            ->get();

        return view('analista.dashboard', compact('countPendentes', 'countConcluidas', 'vistorias'));
    }

    /**
     * Lista todas as vistorias que o analista precisa realizar.
     */
    public function vistoriasPendentes()
    {
        $analystId = Auth::guard('analyst')->id();

        $vistorias = ServiceOrder::with(['contact.user', 'contact.status'])
            ->where('analyst_id', $analystId)
            ->where('destino', 'analista')
            ->latest()
            ->get();

        return view('analista.vistorias-pendentes', compact('vistorias'));
    }

    /**
     * Histórico de vistorias já concluídas pelo analista.
     */
    public function ordensEnviadas()
    {
        $analystId = Auth::guard('analyst')->id();

        $ordensEnviadas = ServiceOrder::with(['contact', 'service'])
            ->where('analyst_id', $analystId)
            ->where('status', 'analise_concluida')
            ->latest('updated_at')
            ->get();

        return view('analista.ordens-enviadas', compact('ordensEnviadas'));
    }

    /**
     * Salva os dados técnicos da vistoria realizada pelo analista.
     */
    public function storeServiceOrder(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'data_vistoria' => 'required|date|before_or_equal:today', 
            'data_execucao' => 'nullable|date|after_or_equal:today',  
        ]);

        $os = ServiceOrder::where('contact_id', $request->contact_id)->firstOrFail();

        // Processa a lista de espécies (converte string separada por vírgula em array)
        $especies = $request->especies;
        if (is_string($especies) && !empty($especies)) {
            $especies = array_map('trim', explode(',', $especies));
        }

        // Atualiza a Ordem de Serviço com os dados técnicos
        $os->update([
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'especies'      => $especies, 
            'quantidade'    => $request->quantidade,
            'data_vistoria' => $request->data_vistoria ?? now(),
            'data_execucao' => $request->data_execucao,
            'motivos'       => $request->motivo ?? null,
            'servicos'      => $request->servico ?? null,
            'equipamentos'  => $request->equip ?? null,
            'observacoes'   => $request->observacoes ?? null,
            'supervisor_id' => Auth::guard('analyst')->id(),
            'destino'       => null, 
            'status'        => 'analise_concluida'
        ]);

        // Atualiza o status da solicitação original para "Vistoriado"
        $statusVistoriado = Status::where('name', 'Vistoriado')->first();
        if ($statusVistoriado) {
            $os->contact->update(['status_id' => $statusVistoriado->id]);
        }

        return redirect()->route('analyst.dashboard')
            ->with('success', 'Vistoria concluída! Dados salvos com sucesso.');
    }

    /**
     * Permite que o cidadão cancele sua própria solicitação.
     * Só é permitido se o status ainda for "Em Análise".
     */
    public function cancelRequest(Contact $contact)
    {
        // Segurança: Verifica se a solicitação é mesmo do usuário logado
        if ($contact->user_id !== Auth::id()) {
            return back()->withErrors(['cancel_error' => 'Ação não autorizada.']);
        }

        // Regra de Negócio: Só cancela se ainda não começou a ser processada
        if ($contact->status->name !== 'Em Análise') {
            return back()->withErrors(['cancel_error' => 'Esta solicitação não pode mais ser cancelada.']);
        }

        // Deleta permanentemente para não poluir o sistema
        $contact->delete();

        return redirect()->route('contact.myrequests')->with('success', 'Solicitação cancelada com sucesso.');
    }
}
