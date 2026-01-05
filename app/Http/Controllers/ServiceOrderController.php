<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;

class ServiceOrderController extends Controller
{
    /**
     * ADMIN -> LISTA OS
     * Enviadas  = Admin enviou p/ Analista (status 'enviada')
     * Recebidas = Analista preencheu e devolveu (status 'recebida')
     */
    public function index(Request $request)
    {
        $tipo = $request->get('tipo', 'recebidas'); // Padrão recebidas

        $oss = ServiceOrder::with(['contact.user', 'contact.status'])
            ->when($tipo === 'recebidas', fn($q) => $q->where('status', 'recebida'))
            ->when($tipo === 'enviadas', fn($q) => $q->where('status', 'enviada'))
            ->latest()
            ->get();

        return view('admin.os.index', compact('oss', 'tipo'));
    }

    /**
     * ADMIN → VISUALIZA OS
     */
    public function show($id)
    {
        $os = ServiceOrder::with(['contact.user', 'contact.status'])->findOrFail($id);
        return view('admin.os.show', compact('os'));
    }

    /**
     * ADMIN → ENVIA OS PARA EQUIPE
     * - muda status da OS
     * - muda status do contato para Em Execução
     */
    public function enviarParaServico($id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);

        if ($os->status === 'enviada') {
            return back()->with('error', 'OS já enviada');
        }

        $os->update(['status' => 'enviada']);

        $os->contact->update([
            'status_id' => \App\Models\Status::where('name', 'Em Execução')->first()->id
        ]);

        return redirect()
            ->route('admin.os.index', ['tipo' => 'recebidas'])
            ->with('success', 'OS enviada para a equipe');
    }
}
