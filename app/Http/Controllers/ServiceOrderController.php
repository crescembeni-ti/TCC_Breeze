<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\Status;
use Illuminate\Http\Request;

/**
 * Controlador responsável pela gestão técnica das Ordens de Serviço (OS) no painel administrativo.
 * Diferente do ContactController que foca na triagem, este controlador foca no preenchimento 
 * de dados técnicos, controle de prazos e fluxo de envio para execução em campo.
 */
class ServiceOrderController extends Controller
{
    /**
     * Lista as Ordens de Serviço separadas por abas de status.
     * 
     * Blocos de lógica:
     * - Filtro 'tipo': Alterna entre OS "Recebidas" (que estão com o Admin ou voltaram da vistoria) 
     *   e "Enviadas" (que estão em trânsito com Analistas ou Equipes de Serviço).
     * - Relacionamentos: Carrega dados do contato, usuário solicitante e status atual.
     */
    public function index(Request $request)
    {
        $tipo = $request->get('tipo', 'recebidas'); 

        $oss = ServiceOrder::with(['contact.user', 'contact.status'])
            ->when($tipo === 'recebidas', fn($q) => $q->whereNull('destino')->orWhere('status', 'analise_concluida'))
            ->when($tipo === 'enviadas', fn($q) => $q->whereIn('destino', ['analista', 'servico']))
            ->latest()
            ->get();

        return view('admin.os.index', compact('oss', 'tipo'));
    }

    /**
     * Exibe os detalhes técnicos de uma Ordem de Serviço específica.
     */
    public function show($id)
    {
        $os = ServiceOrder::with(['contact.user', 'contact.status'])->findOrFail($id);
        return view('admin.os.show', compact('os'));
    }

    /**
     * Processa a atualização dos dados técnicos da OS.
     * 
     * Blocos de lógica:
     * - Bloqueio de Edição: Impede alterações se a equipe de campo já iniciou a execução ou concluiu o serviço.
     * - Cenário de Vistoria: Se a OS está com um Analista, o Admin só pode editar o campo de observações.
     * - Validação de Datas: Garante que a vistoria não seja futura e a execução não seja no passado.
     * - Fluxo Automático: Ao preencher espécies e serviços, o sistema entende que a análise técnica 
     *   foi concluída e atualiza o status do contato para "Vistoriado".
     */
    public function update(Request $request, $id)
    {
        $os = ServiceOrder::findOrFail($id);
        $statusContato = $os->contact->status->name ?? '';

        // Regra de Integridade: Impede edição de processos em andamento ou finalizados
        if ($statusContato === 'Em Execução' || $statusContato === 'Concluído') {
             return back()->with('error', 'Esta OS já está em execução/concluída e não pode ser editada.');
        }

        // Regra de Permissão: Limita edição enquanto o analista está com o processo
        if ($os->destino === 'analista') {
            $os->update(['observacoes' => $request->observacoes]);
            return back()->with('success', 'Observação atualizada (Aguardando retorno do Analista).');
        }

        // Validação dos campos técnicos e cronológicos
        $request->validate([
            'data_vistoria' => 'nullable|date|before_or_equal:today', 
            'data_execucao' => 'nullable|date|after_or_equal:today',
        ], [
            'data_vistoria.before_or_equal' => 'A data da vistoria não pode ser futura.',
            'data_execucao.after_or_equal' => 'A previsão de execução deve ser hoje ou futura.'
        ]);

        // Atualização massiva dos dados técnicos
        $os->update([
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'especies'      => $request->especies,
            'quantidade'    => $request->quantidade,
            'motivos'       => $request->motivos,
            'servicos'      => $request->servicos,
            'equipamentos'  => $request->equipamentos,
            'data_vistoria' => $request->data_vistoria,
            'data_execucao' => $request->data_execucao,
            'observacoes'   => $request->observacoes,
        ]);

        // Lógica de transição de status baseada no preenchimento técnico
        if ($request->filled('especies') && $request->filled('servicos')) {
            if ($os->destino !== 'servico') {
                $os->update(['status' => 'analise_concluida']);
            }
            
            // Sincroniza o status do contato para refletir que a vistoria técnica foi feita
            $statusVistoriado = \App\Models\Status::where('name', 'Vistoriado')->first();
            if ($statusVistoriado && $os->contact->status_id != $statusVistoriado->id && $statusContato != 'Em Execução') {
                $os->contact->update(['status_id' => $statusVistoriado->id]);
            }
        }

        return back()->with('success', 'Ordem de Serviço atualizada.');
    }

    /**
     * Encaminha a OS definitivamente para a execução pela equipe de campo.
     * 
     * Blocos de lógica:
     * - Verificação de Destino: Evita reenvios duplicados.
     * - Verificação de Dados: Garante que a equipe de campo receba informações sobre espécies e serviços.
     * - Mudança de Fluxo: Altera o status para 'pendente_aceite', aguardando o "visto" da equipe técnica.
     */
    public function enviarParaServico($id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);

        if ($os->destino === 'servico') {
            return back()->with('error', 'OS já foi enviada para a equipe.');
        }

        // Garante que a OS não vá para campo sem informações básicas
        if (empty($os->especies) || empty($os->servicos)) {
            return back()->with('error', 'Preencha os dados técnicos antes de enviar.');
        }

        $os->update([
            'status' => 'pendente_aceite',
            'destino' => 'servico'
        ]);

        return redirect()
            ->route('admin.os.index', ['tipo' => 'recebidas'])
            ->with('success', 'OS enviada para a equipe técnica (Aguardando Visto).');
    }
}
