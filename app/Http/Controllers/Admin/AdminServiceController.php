<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Status;

class AdminServiceController extends Controller
{
    // OS prontas para enviar
    public function ordensPendentes()
    {
        $os = Contact::whereNotNull('data_vistoria')
                     ->whereHas('status', fn($q) =>
                         $q->where('name', 'Deferido')
                     )
                     ->get();

        return view('admin.os.pendentes', compact('os'));
    }

    // Admin envia ao serviço
    public function enviarParaServico($id)
    {
        $contact = Contact::findOrFail($id);

        // Mantém DEFERIDO, apenas registra que foi enviado
        $contact->update([
            'designated_to' => 'service', // ou id do funcionário, se desejar
        ]);

        return back()->with('success', 'Ordem de serviço enviada à equipe.');
    }

    // Admin recebe os resultados do Serviço
    public function resultados()
    {
        $os = Contact::whereHas('status', fn($q) =>
            $q->whereIn('name', ['Concluído','Indeferido'])
        )->get();

        return view('admin.os.resultados', compact('os'));
    }
}
