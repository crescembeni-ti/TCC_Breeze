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
     * - Filtro por DESTINO: analista | servico
     */
    public function index(Request $request)
    {
        // 1. Define o destino padrão
        $destino = $request->get('destino', 'analista');

        // 2. Segurança
        if (!in_array($destino, ['analista', 'servico'])) {
            $destino = 'analista';
        }

        // 3. Busca as Ordens de Serviço filtrando pela coluna 'destino'
        // CORREÇÃO: Usamos 'destino' em vez de 'flow' para bater com seu banco
        $oss = ServiceOrder::with('contact.user')
            ->where('destino', $destino) 
            ->orderByDesc('id')
            ->get();

        return view('admin.os.index', compact('oss', 'destino'));
    }

    /**
     * VISUALIZAÇÃO DA OS
     */
    public function show($id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);
        return view('admin.os.show', compact('os'));
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

            $os->update([
                'analyst_id' => null,
                'destino' => null, // Limpa o destino
            ]);
        }

        // Cancelamento de OS enviada ao SERVIÇO
        if ($os->destino === 'servico') {
            $statusVistoriado = Status::where('name', 'Vistoriado')->first();
             if($statusVistoriado) {
                $os->contact->update(['status_id' => $statusVistoriado->id]);
            }

            $os->update([
                'service_id' => null,
                'destino' => null, // Limpa o destino
            ]);
        }

        return back()->with('success', 'Ordem de serviço cancelada com sucesso.');
    }
}