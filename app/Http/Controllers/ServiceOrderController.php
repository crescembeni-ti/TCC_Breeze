<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\Status;
use Illuminate\Http\Request;

class ServiceOrderController extends Controller
{
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

    public function show($id)
    {
        $os = ServiceOrder::with(['contact.user', 'contact.status'])->findOrFail($id);
        return view('admin.os.show', compact('os'));
    }

    // --- ATUALIZAÇÃO DA OS (LÓGICA AJUSTADA) ---
    public function update(Request $request, $id)
    {
        $os = ServiceOrder::findOrFail($id);
        $statusContato = $os->contact->status->name ?? '';

        // BLOQUEIO TOTAL: Se a equipe já deu o VISTO ou CONCLUIU
        if ($statusContato === 'Em Execução' || $statusContato === 'Concluído') {
             return back()->with('error', 'Esta OS já está em execução/concluída e não pode ser editada.');
        }

        // CENÁRIO 1: OS está com o Analista (Aguardando retorno)
        // Admin só pode adicionar/editar observações
        if ($os->destino === 'analista') {
            $os->update(['observacoes' => $request->observacoes]);
            return back()->with('success', 'Observação atualizada (Aguardando retorno do Analista).');
        }

        // CENÁRIO 2: Edição Completa Permitida
        // (Está com Admin, OU com Serviço pendente de aceite, OU Vistoriado)
        
        $request->validate([
            // Impede data futura na vistoria (validação back-end também)
            'data_vistoria' => 'nullable|date|before_or_equal:today', 
            'data_execucao' => 'nullable|date|after_or_equal:today',
        ], [
            'data_vistoria.before_or_equal' => 'A data da vistoria não pode ser futura.',
            'data_execucao.after_or_equal' => 'A previsão de execução deve ser hoje ou futura.'
        ]);

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

        // Se preencheu dados técnicos, considera "Vistoriado" (para fluxo direto)
        if ($request->filled('especies') && $request->filled('servicos')) {
            // Só muda status interno se não estiver enviada para serviço
            if ($os->destino !== 'servico') {
                $os->update(['status' => 'analise_concluida']);
            }
            
            // Garante status do contato (se não estiver em execução)
            $statusVistoriado = \App\Models\Status::where('name', 'Vistoriado')->first();
            if ($statusVistoriado && $os->contact->status_id != $statusVistoriado->id && $statusContato != 'Em Execução') {
                $os->contact->update(['status_id' => $statusVistoriado->id]);
            }
        }

        return back()->with('success', 'Ordem de Serviço atualizada.');
    }

    public function enviarParaServico($id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);

        if ($os->destino === 'servico') {
            return back()->with('error', 'OS já foi enviada para a equipe.');
        }

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