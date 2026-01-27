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

class ContactController extends Controller
{
    /* ============================================================
     * ÁREA DO CIDADÃO (USUÁRIO COMUM)
     * ============================================================ */
    
    // 1. Formulário de Contato
    public function index()
    {
        return view('pages.contact', [
            'bairros' => Bairro::orderBy('nome')->get(),
            'topicos' => Topico::orderBy('nome')->get(),
        ]);
    }

    // 2. Salvar Solicitação
    public function store(Request $request)
    {
        $request->validate([
            'topico' => 'required|string|max:255',
            'telefone' => 'required|string|max:20', 
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

    // 3. Minhas Solicitações
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

    // 4. Listagem Principal (Admin) com Filtros Avançados
    // 4. Listagem Principal (Admin) com Filtros Avançados
    // 4. Listagem Principal (Admin) com Filtros Avançados
    public function adminContactList(Request $request)
    {
        $filtro = $request->get('filtro', 'pendentes');

        // 1. Query Base (Comum para mapa e tabela - Filtra apenas DATA)
        $baseQuery = Contact::with(['status', 'user', 'serviceOrder.service']);

        // --- Filtro de Período (Aplica a ambos) ---
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

        // 2. DADOS DO MAPA (Clona a base + ignora as abas)
        // Isso garante que o mapa mostre TUDO do período selecionado
        $mapMessages = (clone $baseQuery)->get();

        // 3. DADOS DA TABELA (Aplica o filtro da aba + ORDENAÇÃO)
        $tableQuery = $baseQuery; 

        if ($filtro === 'pendentes') {
            $tableQuery->whereHas('status', fn ($q) =>
                $q->whereIn('name', ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'])
            );
            // Regra de visibilidade para pendentes
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
        // Se for 'todas', não aplica whereHas extra, pega tudo.

        // ITEM 1: Ordenação latest() aqui garante que o mais recente venha primeiro
        $messages = $tableQuery->latest()->get(); 

        return view('admin.contacts.index', [
            'messages' => $messages,       // Lista da Tabela (Ordenada)
            'mapMessages' => $mapMessages, // Lista do Mapa (Completa)
            'allStatuses' => Status::where('name', '!=', 'Cancelado')->get(),
            'analistas' => Analyst::orderBy('name')->get(),
            'servicos' => Service::orderBy('name')->get(),
            'filtro' => $filtro,
        ]);
    }

    // 5. Atualizar Status Manualmente
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

    // 6. Rota Central do Modal de Encaminhamento
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

    // 7. Enviar para Analista
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

    // 8. Enviar para Serviço
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
    
    // 9. Dashboard do Analista
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

    // 10. Lista Completa de Vistorias Pendentes
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

    // 11. Histórico de Ordens Enviadas
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

    // 12. Salvar Vistoria
    public function storeServiceOrder(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'data_vistoria' => 'required|date|before_or_equal:today', 
            'data_execucao' => 'nullable|date|after_or_equal:today',  
        ], [
            'data_vistoria.before_or_equal' => 'A data da vistoria não pode ser no futuro.',
            'data_execucao.after_or_equal' => 'A previsão de execução não pode ser no passado.',
        ]);

        $os = ServiceOrder::where('contact_id', $request->contact_id)->firstOrFail();

        $especies = $request->especies;
        if (is_string($especies) && !empty($especies)) {
            $especies = array_map('trim', explode(',', $especies));
        }

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

        $statusVistoriado = Status::where('name', 'Vistoriado')->first();
        if ($statusVistoriado) {
            $os->contact->update(['status_id' => $statusVistoriado->id]);
        }

        return redirect()->route('analyst.dashboard')
            ->with('success', 'Vistoria concluída! Dados salvos com sucesso.');
    }

    // 13. Cancelar Solicitação (Usuário)
    public function cancelRequest(Contact $contact)
    {
        // Verifica se a solicitação pertence ao usuário logado
        if ($contact->user_id !== Auth::id()) {
            return back()->withErrors(['cancel_error' => 'Ação não autorizada.']);
        }

        // Verifica se o status permite cancelamento (Apenas "Em Análise")
        if ($contact->status->name !== 'Em Análise') {
            return back()->withErrors(['cancel_error' => 'Esta solicitação não pode mais ser cancelada.']);
        }

        // Deleta a solicitação permanentemente (conforme pedido: não aparece mais para o usuário nem para o admin)
        $contact->delete();

        return redirect()->route('contact.myrequests')->with('success', 'Solicitação cancelada com sucesso.');
    }
}