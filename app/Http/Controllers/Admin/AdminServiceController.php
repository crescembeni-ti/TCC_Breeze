<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\Status;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    /**
     * LISTAGEM PRINCIPAL
     */
    public function index(Request $request)
    {
        // 1. Define o destino padrão (analista ou servico)
        $destino = $request->get('destino', 'analista');

        if (!in_array($destino, ['analista', 'servico'])) {
            $destino = 'analista';
        }

        // 2. Monta a Query Base
        $query = ServiceOrder::with(['contact.user', 'contact.status'])
            ->where('destino', $destino);

        // --- AQUI ESTÁ A LÓGICA QUE VOCÊ PEDIU ---
        // Se estamos vendo a lista de enviados para o SERVIÇO:
        if ($destino === 'servico') {
            // Esconde as ordens que já estão "Em Execução".
            // Assim, elas somem desta tela assim que o serviço dá o "Visto",
            // mas continuam existindo para a equipe técnica trabalhar.
            $query->whereHas('contact.status', function ($q) {
                $q->where('name', '!=', 'Em Execução'); 
            });
        }

        $oss = $query->orderByDesc('id')->get();

        return view('admin.os.index', compact('oss', 'destino'));
    }

    /**
     * VISUALIZAÇÃO DA OS
     */
    public function show($id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);
        
        // Arrays para visualização (ajuste conforme seus dados reais)
        $todosMotivos = [
            'Risco de Queda' => 'Risco de Queda',
            'Conflito rede eletrica' => 'Conflito com rede elétrica',
            'Danos infraestrutura' => 'Danos à infraestrutura',
            'Outras' => 'Outras razões'
        ];
        
        $todosServicos = [
            'Levantamento copa' => 'Poda de levantamento de copa',
            'Desobstrucao' => 'Poda de desobstrução de rede',
            'Limpeza' => 'Poda de limpeza',
            'Adequacao' => 'Poda de adequação',
            'Remocao Total' => 'Remoção total da árvore',
            'Outras' => 'Outras intervenções'
        ];

        $todosEquipamentos = [
            'Motosserra' => 'Motosserra',
            'Motopoda' => 'Motopoda',
            'EPIs' => 'EPIs',
            'Cordas' => 'Cordas',
            'Cones' => 'Cones',
            'Caminhão' => 'Caminhão'
        ];

        return view('admin.os.show', compact('os', 'todosMotivos', 'todosServicos', 'todosEquipamentos'));
    }

    /**
     * CANCELAR ENVIO DA OS
     */
    public function cancelar($id)
    {
        $os = ServiceOrder::findOrFail($id);

        // Cancelamento de OS enviada ao ANALISTA
        if ($os->destino === 'analista') {
            $statusDeferido = Status::where('name', 'Deferido')->first();
            if($statusDeferido) {
                $os->contact->update(['status_id' => $statusDeferido->id]);
            }
            $os->update(['analyst_id' => null, 'destino' => null]);
        }

        // Cancelamento de OS enviada ao SERVIÇO
        if ($os->destino === 'servico') {
            $statusVistoriado = Status::where('name', 'Vistoriado')->first();
             if($statusVistoriado) {
                $os->contact->update(['status_id' => $statusVistoriado->id]);
            }
            $os->update(['service_id' => null, 'destino' => null]);
        }

        return back()->with('success', 'Ordem de serviço cancelada com sucesso.');
    }
    
    // Métodos extras (pendentes, resultados, enviarParaServico) mantenha se você usa
    public function ordensPendentes() { return view('admin.os.pendentes'); } 
    public function resultados() { return view('admin.os.resultados'); }
    public function enviarParaServico(Request $request, $id) { /* ... */ }
}