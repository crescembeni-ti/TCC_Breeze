<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Status;
use Illuminate\Http\Request;

class ServiceExecutionController extends Controller
{
    public function index()
    {
        // TAREFAS = OS que o admin enviou para o serviço (pelo designated_to)
        $tarefas = Contact::where('designated_to', 'service')
                          ->whereHas('status', fn($q) =>
                              $q->where('name', 'Deferido')
                          )
                          ->get();

        return view('servico.tarefas', compact('tarefas'));
    }

    // Serviço concluiu
    public function concluir($id)
    {
        $contact = Contact::findOrFail($id);

        $status = Status::where('name', 'Concluído')->firstOrFail();

        $contact->update([
            'status_id' => $status->id,
            'data_execucao' => now(),
        ]);

        return back()->with('success', 'Serviço concluído com sucesso.');
    }

    // Serviço não conseguiu fazer
    public function falha(Request $request, $id)
    {
        $request->validate([
            'motivo_falha' => 'required|min:5'
        ]);

        $contact = Contact::findOrFail($id);

        $status = Status::where('name', 'Indeferido')->firstOrFail();

        $contact->update([
            'status_id' => $status->id,
            'justificativa' => $request->motivo_falha,
        ]);

        return back()->with('warning', 'Falha registrada.');
    }
}
