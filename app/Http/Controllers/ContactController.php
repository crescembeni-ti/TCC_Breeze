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
 * Controlador responsável pela gestão de solicitações de serviço (Contatos).
 * Este é um dos principais controladores do sistema, gerenciando o fluxo de vida de um pedido:
 * 1. Criação pelo Cidadão.
 * 2. Triagem pelo Administrador.
 * 3. Vistoria pelo Analista.
 * 4. Encaminhamento para Execução.
 */
class ContactController extends Controller
{
    /* ============================================================
     * ÁREA DO CIDADÃO (USUÁRIO FINAL)
     * ============================================================ */
    
    /**
     * Exibe o formulário de solicitação de serviço para o cidadão.
     * Carrega as listas de bairros e tópicos (assuntos) para preencher os campos de seleção.
     */
    public function index()
    {
        return view('pages.contact', [
            'bairros' => Bairro::orderBy('nome')->get(),
            'topicos' => Topico::orderBy('nome')->get(),
        ]);
    }

    /**
     * Processa e armazena uma nova solicitação enviada pelo cidadão.
     * 
     * Blocos de lógica:
     * - Validação: Garante que os campos obrigatórios e fotos estejam no formato correto.
     * - Upload: Salva até 3 imagens no disco 'public' dentro da pasta 'solicitacoes'.
     * - Persistência: Cria o registro vinculando ao usuário logado e define o status inicial "Em Análise".
     */
    public function store(Request $request)
    {
        // Regras de validação para os dados recebidos do formulário
        $request->validate([
            'topico' => 'required|string|max:255',
            'telefone' => 'required|string|max:20', 
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'descricao' => 'required|string',
            'fotos' => 'nullable|array|max:3',
            'fotos.*' => 'image|max:2048',
        ]);

        // Gerenciamento de arquivos de imagem
        $paths = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $paths[] = $foto->store('solicitacoes', 'public');
            }
        }

        // Inserção no banco de dados com dados do solicitante extraídos do Auth
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
     * Recupera e exibe a lista de solicitações feitas pelo próprio usuário.
     * Filtra para não mostrar solicitações que foram canceladas.
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
     * ÁREA ADMINISTRATIVA (GESTÃO E TRIAGEM)
     * ============================================================ */

    /**
     * Painel principal do Administrador para gerenciar todos os contatos.
     * 
     * Funcionalidades:
     * - Filtro Temporal: Permite ver solicitações de hoje, 7 dias, 30 dias ou datas específicas.
     * - Abas de Status: Separa o que está "Pendente" do que já foi "Resolvido".
     * - Mapa: Prepara os dados geográficos para exibição no mapa de calor/pontos.
     */
    public function adminContactList(Request $request)
    {
        $filtro = $request->get('filtro', 'pendentes');

        // Inicializa a query com os relacionamentos para evitar o problema N+1
        $baseQuery = Contact::with(['status', 'user', 'serviceOrder.service']);

        // Lógica de filtragem por período de tempo usando Carbon
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

        // Clone da query para o mapa (independente do filtro de abas)
        $mapMessages = (clone $baseQuery)->get();

        // Aplicação de filtros de status conforme a aba selecionada
        $tableQuery = $baseQuery; 

        if ($filtro === 'pendentes') {
            // Considera pendente tudo que ainda está em fluxo operacional
            $tableQuery->whereHas('status', fn ($q) =>
                $q->whereIn('name', ['Em Análise', 'Deferido', 'Vistoriado', 'Em Execução'])
            );
            
            // Regra específica: oculta o que já foi encaminhado e não está "Em Execução"
            $tableQuery->where(function ($mainQuery) {
                $mainQuery->whereDoesntHave('serviceOrder', function ($q) {
                    $q->whereIn('destino', ['analista', 'servico']);
                })
                ->orWhereHas('status', function ($q) {
                    $q->where('name', 'Em Execução');
                });
            });
        } elseif ($filtro === 'resolvidas') {
            // Considera resolvida as solicitações finalizadas ou negadas
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
     * Altera o status de uma solicitação e registra uma justificativa se necessário.
     * Exemplo: Se for marcar como 'Indeferido', a justificativa torna-se obrigatória.
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
     * FLUXO DE ENCAMINHAMENTO (ADMIN -> ANALISTA/EQUIPE)
     * ============================================================ */

    /**
     * Ponto de decisão: Encaminha o pedido para vistoria técnica ou para execução direta.
     */
    public function forward(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        // Se houver ID de analista na requisição, chama o método de envio para analista
        if ($request->has('analyst_id')) return $this->sendToAnalyst($request, $contact);
        
        // Se houver ID de serviço, cria/busca a OS e envia para a equipe de campo
        if ($request->has('service_id')) {
            $os = ServiceOrder::firstOrCreate(['contact_id' => $contact->id]);
            return $this->sendToService($request, $os);
        }

        return response()->json(['message' => 'Nenhum destino selecionado.'], 400);
    }

    /**
     * Vincula um analista à solicitação e cria/atualiza a Ordem de Serviço com destino 'analista'.
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
     * Vincula uma equipe de serviço à Ordem de Serviço e define o status como 'pendente_aceite'.
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
     * ÁREA DO ANALISTA (VISTORIA TÉCNICA)
     * ============================================================ */
    
    /**
     * Dashboard específico para o Analista logado.
     * Mostra contadores de vistorias pendentes e concluídas, além de uma lista rápida.
     */
    public function analystDashboard()
    {
        $analystId = Auth::guard('analyst')->id();

        // Contagem de ordens aguardando vistoria
        $countPendentes = ServiceOrder::where('analyst_id', $analystId)
            ->where('destino', 'analista')
            ->count();

        // Contagem de vistorias já finalizadas
        $countConcluidas = ServiceOrder::where('analyst_id', $analystId)
            ->where('status', 'analise_concluida')
            ->count();

        // Lista as últimas 5 vistorias pendentes
        $vistorias = ServiceOrder::with(['contact.user']) 
            ->where('analyst_id', $analystId)
            ->where('destino', 'analista')
            ->latest()
            ->limit(5)
            ->get();

        return view('analista.dashboard', compact('countPendentes', 'countConcluidas', 'vistorias'));
    }

    /**
     * Lista completa de todas as vistorias pendentes para o analista logado.
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
     * Exibe o histórico de análises já concluídas pelo profissional.
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
     * Salva o laudo técnico da vistoria.
     * 
     * Blocos de lógica:
     * - Validação: Checa datas e existência da solicitação.
     * - Tratamento de Dados: Converte a string de espécies em um array JSON.
     * - Atualização: Salva coordenadas, equipamentos necessários e observações técnicas.
     * - Fluxo: Muda o status da solicitação original para "Vistoriado".
     */
    public function storeServiceOrder(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'data_vistoria' => 'required|date|before_or_equal:today', 
            'data_execucao' => 'nullable|date|after_or_equal:today',  
        ]);

        $os = ServiceOrder::where('contact_id', $request->contact_id)->firstOrFail();

        // Conversão de espécies: transforma "Ipê, Carvalho" em ["Ipê", "Carvalho"]
        $especies = $request->especies;
        if (is_string($especies) && !empty($especies)) {
            $especies = array_map('trim', explode(',', $especies));
        }

        // Persistência dos dados do laudo na Ordem de Serviço
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

        // Sincronização do status da solicitação pai
        $statusVistoriado = Status::where('name', 'Vistoriado')->first();
        if ($statusVistoriado) {
            $os->contact->update(['status_id' => $statusVistoriado->id]);
        }

        return redirect()->route('analyst.dashboard')
            ->with('success', 'Vistoria concluída! Dados salvos com sucesso.');
    }

    /**
     * Permite ao cidadão cancelar sua própria solicitação, desde que o processo ainda não tenha iniciado.
     */
    public function cancelRequest(Contact $contact)
    {
        // Proteção: Garante que um usuário não cancele o pedido de outro
        if ($contact->user_id !== Auth::id()) {
            return back()->withErrors(['cancel_error' => 'Ação não autorizada.']);
        }

        // Proteção: Impede o cancelamento se a prefeitura já iniciou o atendimento
        if ($contact->status->name !== 'Em Análise') {
            return back()->withErrors(['cancel_error' => 'Esta solicitação não pode mais ser cancelada.']);
        }

        // Remoção física do registro
        $contact->delete();

        return redirect()->route('contact.myrequests')->with('success', 'Solicitação cancelada com sucesso.');
    }
}
