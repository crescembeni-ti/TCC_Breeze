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
     * - Filtro por destino: analista | servico
     */
    public function index(Request $request)
    {
        // analista é o filtro padrão
        $destino = $request->get('destino', 'analista');

        // Segurança: só aceita valores válidos
        if (!in_array($destino, ['analista', 'servico'])) {
            abort(404);
        }

        $oss = ServiceOrder::with('contact.user')
            ->where('flow', $destino)
            ->orderByDesc('id')
            ->get();

        return view('admin.os.index', compact('oss', 'destino'));
    }

    /**
     * VISUALIZAÇÃO DA OS
     */
    public function show(ServiceOrder $os)
    {
        return view('admin.os.show', compact('os'));
    }

    /**
     * CANCELAR ENVIO DA OS
     *
     * - Analista → volta para DEFERIDO
     * - Serviço  → volta para VISTORIADO
     */
    public function cancelar(ServiceOrder $os)
    {
        // Cancelamento de OS enviada ao ANALISTA
        if ($os->flow === 'analista') {

            $status = Status::where('name', 'Deferido')->firstOrFail();

            $os->update([
                'analyst_id' => null,
                'flow' => null, // remove do filtro
            ]);

            $os->contact->update([
                'status_id' => $status->id,
            ]);
        }

        // Cancelamento de OS enviada ao SERVIÇO
        if ($os->flow === 'servico') {

            $status = Status::where('name', 'Vistoriado')->firstOrFail();

            $os->update([
                'service_id' => null,
                'flow' => null, // remove do filtro
            ]);

            $os->contact->update([
                'status_id' => $status->id,
            ]);
        }

        return back()->with('success', 'Ordem de serviço cancelada com sucesso.');
    }
}
