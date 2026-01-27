<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceOrder;
use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;

class ServiceExecutionController extends Controller
{
    // 1. RECEBIDAS (Status: Vistoriado)
    public function recebidas()
    {
        $user = Auth::guard('service')->user();

        $ordens = ServiceOrder::with(['contact.status', 'contact'])
            ->where('service_id', $user->id)
            ->whereHas('contact.status', function (Builder $query) {
                // Filtra EXATAMENTE pelo nome do status
                $query->where('name', 'Vistoriado');
            })
            // Garante que a OS interna também não esteja concluída ou em execução (dupla checagem)
            ->where(function($q) {
                $q->where('status', 'pendente_aceite')
                  ->orWhere('status', 'enviada')
                  ->orWhereNull('status');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('servico.tarefas-recebidas', compact('ordens'));
    }

    // 2. EM ANDAMENTO (Status: Em Execução)
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

    // 3. CONCLUÍDAS (Status: Concluído)
    public function concluidas()
    {
        $user = Auth::guard('service')->user();

        $ordens = ServiceOrder::with(['contact.status', 'contact'])
            ->where('service_id', $user->id)
            // Aqui filtramos pelo status interno da OS para garantir histórico
            ->where('status', 'concluido')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('servico.tarefas-concluidas', compact('ordens'));
    }

    // --- AÇÕES ---

    public function confirmarRecebimento($id)
    {
        $os = ServiceOrder::findOrFail($id);
        
        // 1. Busca o ID correto do status "Em Execução" no banco
        $statusEmExecucao = Status::where('name', 'Em Execução')->first();

        if (!$statusEmExecucao) {
            return redirect()->back()->with('error', 'Erro: Status "Em Execução" não encontrado no banco de dados.');
        }

        // 2. Atualiza o status do CONTATO (Isso faz aparecer na aba "Em Andamento" e atualiza pro Admin)
        $os->contact->status_id = $statusEmExecucao->id;
        $os->contact->save();

        // 3. Atualiza o status interno da OS e a data de visualização
        $os->status = 'em_execucao';
        
        // AQUI ESTÁ A ALTERAÇÃO SOLICITADA:
        $os->data_do_visto = now(); 
        
        $os->save();

        // Redireciona para a lista correta
        return redirect()->route('service.tasks.em_andamento')
            ->with('success', 'Ordem iniciada! Movida para "Em Andamento".');
    }

    public function concluir(Request $request, $id)
    {
        $os = ServiceOrder::findOrFail($id);
        
        // 1. Busca o ID correto do status "Concluído"
        $statusConcluido = Status::where('name', 'Concluído')->first();

        if (!$statusConcluido) {
            return redirect()->back()->with('error', 'Erro: Status "Concluído" não encontrado no banco.');
        }

        // 2. Atualiza status interno da OS
        $os->status = 'concluido';
        $os->save();

        // 3. Atualiza o status do CONTATO (Isso faz cair no filtro "Resolvidas" do Admin)
        $os->contact->status_id = $statusConcluido->id;
        $os->contact->save();

        return redirect()->route('service.tasks.concluidas')
            ->with('success', 'Serviço concluído com sucesso!');
    }

    public function falha(Request $request, $id)
    {
        $os = ServiceOrder::findOrFail($id);
        
        $os->status = 'falha'; 
        // $os->motivo_falha = $request->motivo_falha; 
        $os->save();

        return redirect()->back()->with('error', 'Falha registrada.');
    }
}