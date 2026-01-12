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

    // 4. Listagem Principal (Admin)
    public function adminContactList(Request $request)
    {
        $filtro = $request->get('filtro', 'pendentes');

        $query = Contact::with(['status', 'user', 'serviceOrder']);

        if ($filtro === 'pendentes') {
            // Filtra pelos status básicos
            $query->whereHas('status', fn ($q) =>
                $q->whereIn('name', ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'])
            );

            // CORREÇÃO AQUI: 
            // Antes escondíamos ['analista', 'servico']. 
            // Agora escondemos SÓ 'analista'.
            // Assim, se estiver com o 'servico' (Em Execução), o Admin CONSEGUE ver.
            $query->whereDoesntHave('serviceOrder', function ($q) {
                $q->where('destino', 'analista'); 
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
        
        // Define destino='analista' -> Some da lista principal e vai para a tela de OS
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
        
        // Define destino='servico' -> Vai para a tela de OS
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

        // Contadores
        $countPendentes = ServiceOrder::where('analyst_id', $analystId)
            ->where('destino', 'analista')
            ->count();

        $countConcluidas = ServiceOrder::where('analyst_id', $analystId)
            ->where('status', 'analise_concluida')
            ->count();

        // Lista recente para a tabela no Dashboard (CORRIGIDO: variavel $vistorias)
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

        // Carrega contact.user e contact.status para exibir na tabela
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

        // CORRIGIDO: Nome da variável deve ser $ordensEnviadas para bater com a View
        $ordensEnviadas = ServiceOrder::with(['contact', 'service'])
            ->where('analyst_id', $analystId)
            ->where('status', 'analise_concluida')
            ->latest('updated_at')
            ->get();

        return view('analista.ordens-enviadas', compact('ordensEnviadas'));
    }

    // 12. Salvar Vistoria (Devolver para Admin)
    // 12. Salvar Vistoria (CORRIGIDO: CAPTURA TODOS OS CAMPOS)
    public function storeServiceOrder(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'data_vistoria' => 'required|date|before_or_equal:today', // Vistoria: Hoje ou antes
            'data_execucao' => 'nullable|date|after_or_equal:today',  // Execução: Hoje ou depois
        ], [
            // Mensagens personalizadas (opcional)
            'data_vistoria.before_or_equal' => 'A data da vistoria não pode ser no futuro.',
            'data_execucao.after_or_equal' => 'A previsão de execução não pode ser no passado.',
        ]);

        $os = ServiceOrder::where('contact_id', $request->contact_id)->firstOrFail();

        // Tratamento para Especies (se o seu Model espera array, mas o form manda string)
        $especies = $request->especies;
        if (is_string($especies) && !empty($especies)) {
            // Transforma "Ipê, Mangueira" em ["Ipê", "Mangueira"] para evitar erro de Array
            $especies = array_map('trim', explode(',', $especies));
        }

        // ATUALIZA TODOS OS DADOS VINDOS DO FORMULÁRIO
        $os->update([
            // 1. Dados Técnicos (Estavam faltando!)
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'especies'      => $especies, 
            'quantidade'    => $request->quantidade,

            // 2. Datas (Agora pega o que o analista digitou)
            'data_vistoria' => $request->data_vistoria ?? now(), // Usa o input ou data atual se falhar
            'data_execucao' => $request->data_execucao,

            // 3. Checkboxes e Textos
            'motivos'       => $request->motivo ?? null,
            'servicos'      => $request->servico ?? null,
            'equipamentos'  => $request->equip ?? null,
            'observacoes'   => $request->observacoes ?? null,
            
            // 4. Controle de Fluxo
            'supervisor_id' => Auth::guard('analyst')->id(),
            'destino'       => null, 
            'status'        => 'analise_concluida'
        ]);

        // ATUALIZA O STATUS DO CONTATO
        $statusVistoriado = Status::where('name', 'Vistoriado')->first();
        if ($statusVistoriado) {
            $os->contact->update(['status_id' => $statusVistoriado->id]);
        }

        return redirect()->route('analyst.dashboard')
            ->with('success', 'Vistoria concluída! Dados salvos com sucesso.');
    }
}