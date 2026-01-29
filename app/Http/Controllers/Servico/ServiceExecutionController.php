<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceOrder;
use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;

/**
 * Controlador responsável pela execução das Ordens de Serviço (OS) pelas equipes de campo.
 * Este controlador gerencia o ciclo de vida operacional da OS após o encaminhamento pelo Admin:
 * 1. Recebimento e Aceite (Visto).
 * 2. Monitoramento do que está em andamento.
 * 3. Finalização do serviço.
 */
class ServiceExecutionController extends Controller
{
    /**
     * Lista as ordens que acabaram de chegar para a equipe (Aguardando Visto).
     * 
     * Blocos de lógica:
     * - Identificação: Filtra pelo ID da equipe de serviço logada.
     * - Status do Contato: Busca solicitações que estão com o status "Vistoriado".
     * - Status Interno: Garante que a OS esteja em estado de espera ('pendente_aceite').
     */
    public function recebidas()
    {
        $user = Auth::guard('service')->user();

        $ordens = ServiceOrder::with(['contact.status', 'contact'])
            ->where('service_id', $user->id)
            ->whereHas('contact.status', function (Builder $query) {
                $query->where('name', 'Vistoriado');
            })
            ->where(function($q) {
                $q->where('status', 'pendente_aceite')
                  ->orWhere('status', 'enviada')
                  ->orWhereNull('status');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('servico.tarefas-recebidas', compact('ordens'));
    }

    /**
     * Lista as ordens que a equipe já aceitou e estão sendo executadas no momento.
     * Filtra solicitações cujo status do contato é "Em Execução".
     */
    public function emAndamento()
    {
        $user = Auth::guard('service')->user();

        $ordens = ServiceOrder::with(['contact.status', 'contact'])
            ->where('service_id', $user->id)
            ->whereHas('contact.status', function (Builder $query) {
                $query->where('name', 'Em Execução');
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('servico.tarefas-em-andamento', compact('ordens'));
    }

    /**
     * Exibe o histórico de todos os serviços já finalizados pela equipe logada.
     * Utiliza o status interno 'concluido' da Ordem de Serviço para compor o histórico.
     */
    public function concluidas()
    {
        $user = Auth::guard('service')->user();

        $ordens = ServiceOrder::with(['contact.status', 'contact'])
            ->where('service_id', $user->id)
            ->where('status', 'concluido')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('servico.tarefas-concluidas', compact('ordens'));
    }

    /* ============================================================
     * AÇÕES OPERACIONAIS
     * ============================================================ */

    /**
     * Registra o "Visto" da equipe na Ordem de Serviço, iniciando formalmente o trabalho.
     * 
     * Blocos de lógica:
     * - Transição de Status: Altera o status da solicitação pai para "Em Execução".
     * - Auditoria: Registra a data e hora exata em que a equipe visualizou e aceitou o serviço (`data_do_visto`).
     * - Fluxo: Move a tarefa da aba "Recebidas" para "Em Andamento".
     */
    public function confirmarRecebimento($id)
    {
        $os = ServiceOrder::findOrFail($id);
        
        // Localiza o status correto para sincronização
        $statusEmExecucao = Status::where('name', 'Em Execução')->first();

        if (!$statusEmExecucao) {
            return redirect()->back()->with('error', 'Erro: Status "Em Execução" não encontrado no banco de dados.');
        }

        // Sincroniza o status global da solicitação
        $os->contact->status_id = $statusEmExecucao->id;
        $os->contact->save();

        // Atualiza os dados internos de controle da OS
        $os->status = 'em_execucao';
        $os->data_do_visto = now(); 
        $os->save();

        return redirect()->route('service.tasks.em_andamento')
            ->with('success', 'Ordem iniciada! Movida para "Em Andamento".');
    }

    /**
     * Finaliza o serviço e move a Ordem para o histórico de concluídas.
     * 
     * Blocos de lógica:
     * - Fechamento: Altera o status interno para 'concluido'.
     * - Notificação Admin: Altera o status do contato para "Concluído", informando ao administrador 
     *   que a solicitação foi resolvida.
     */
    public function concluir(Request $request, $id)
    {
        $os = ServiceOrder::findOrFail($id);
        
        $statusConcluido = Status::where('name', 'Concluído')->first();

        if (!$statusConcluido) {
            return redirect()->back()->with('error', 'Erro: Status "Concluído" não encontrado no banco.');
        }

        // Finaliza o ciclo de vida da OS
        $os->status = 'concluido';
        $os->save();

        // Atualiza o status final da solicitação original
        $os->contact->status_id = $statusConcluido->id;
        $os->contact->save();

        return redirect()->route('service.tasks.concluidas')
            ->with('success', 'Serviço concluído com sucesso!');
    }

    /**
     * Registra uma falha ou impedimento na execução do serviço.
     * Útil para casos onde a equipe vai ao local mas não consegue realizar a poda/remoção.
     */
    public function falha(Request $request, $id)
    {
        $os = ServiceOrder::findOrFail($id);
        
        $os->status = 'falha'; 
        $os->save();

        return redirect()->back()->with('error', 'Falha registrada.');
    }
}
