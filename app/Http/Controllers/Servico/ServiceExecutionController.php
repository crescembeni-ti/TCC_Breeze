<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Status;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceExecutionController extends Controller
{
    public function index()
    {
        $ordensDeServico = ServiceOrder::with(['contact.status', 'contact']) // Carrega status tb
            ->where('service_id', Auth::id())
            ->where('destino', 'servico')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('servico.tarefas', compact('ordensDeServico'));
    }

    // --- NOVO: CONFIRMAR RECEBIMENTO (VISTO) ---
    public function confirmarRecebimento($id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);

        // Busca o status "Em Execução"
        $statusExecucao = Status::where('name', 'Em Execução')->first();

        if ($statusExecucao) {
            // Atualiza o status do contato para o Admin ver que começou
            $os->contact->update(['status_id' => $statusExecucao->id]);
        }

        return redirect()->route('service.tasks.index')
            ->with('success', 'Ordem de Serviço iniciada! Status atualizado para "Em Execução".');
    }

    // Serviço concluiu a tarefa
    public function concluir($id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);

        $os->update([
            'data_execucao' => now(),
            'status' => 'concluido',
            'destino' => null, 
        ]);

        $statusConcluido = Status::where('name', 'Concluído')->first();
        if ($statusConcluido) {
            $os->contact->update(['status_id' => $statusConcluido->id]);
        }

        return redirect()->route('service.tasks.index')
            ->with('success', 'Serviço concluído! A solicitação foi finalizada.');
    }

    // Serviço não conseguiu fazer
    public function falha(Request $request, $id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);

        $motivo = $request->input('motivo_falha');
        $novaObs = ($os->observacoes ?? '') . " | FALHA REGISTRADA: " . $motivo;

        $os->update([
            'observacoes' => $novaObs,
            'status' => 'impedimento',
            'destino' => null, 
        ]);

        // Se falhou, volta para Em Análise ou mantém Em Execução para o Admin ver
        $statusProblema = Status::where('name', 'Em Execução')->first(); 
        if ($statusProblema) {
            $os->contact->update(['status_id' => $statusProblema->id]);
        }

        return redirect()->route('service.tasks.index')
            ->with('warning', 'Falha registrada e devolvida ao administrador.');
    }
}