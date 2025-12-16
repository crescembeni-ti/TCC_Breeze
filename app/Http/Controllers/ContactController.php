<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro;
use App\Models\Topico;
use App\Models\Analyst;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\ServiceOrder;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ContactController extends Controller
{
    /* ============================================================
     * FORMULÁRIO DO SITE
     * ============================================================ */
    public function index()
    {
        $bairros = Bairro::orderBy('nome')->get();
        $topicos = Topico::orderBy('nome')->get();

        return view('pages.contact', compact('bairros', 'topicos'));
    }

    /* ============================================================
     * USUÁRIO → CRIA SOLICITAÇÃO
     * Status inicial: Em Análise
     * ============================================================ */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'topico' => 'required|string',
            'bairro' => 'required|string',
            'rua' => 'required|string',
            'numero' => 'nullable|string',
            'descricao' => 'required|string',
            'fotos' => 'nullable|array|max:3',
            'fotos.*' => 'image|max:2048',
        ]);

        $fotos = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $fotos[] = $foto->store('solicitacoes', 'public');
            }
        }

        $status = Status::where('name', 'Em Análise')->firstOrFail();

        Contact::create([
            'topico' => $validated['topico'],
            'bairro' => $validated['bairro'],
            'rua' => $validated['rua'],
            'numero' => $validated['numero'],
            'descricao' => $validated['descricao'],
            'fotos' => $fotos,
            'user_id' => $user->id,
            'nome_solicitante' => $user->name,
            'email_solicitante' => $user->email,
            'status_id' => $status->id,
        ]);

        return redirect()->route('contact')->with('success', 'Solicitação enviada!');
    }

    /* ============================================================
     * ADMIN → LISTA SOLICITAÇÕES
     * Pendentes = ainda NÃO enviadas para analista nem serviço
     * ============================================================ */
    public function adminContactList(Request $request)
    {
        $filtro = $request->get('filtro', 'todas');

        $query = Contact::with(['status', 'user']);

        if ($filtro === 'pendentes') {
            $query
                ->whereNull('analyst_id')
                ->whereNull('service_id')
                ->whereHas('status', fn($q) =>
                    $q->whereIn('name', ['Em Análise', 'Deferido'])
                );
        }

        if ($filtro === 'resolvidas') {
            $query->whereHas('status', fn($q) =>
                $q->whereIn('name', ['Concluído', 'Indeferido', 'Sem Pendências'])
            );
        }

        $messages = $query->latest()->get();

        return view('admin.contacts.index', [
            'messages'   => $messages,
            'filtro'     => $filtro,
            'allStatuses'=> Status::where('name', '!=', 'Cancelado')->get(),
            'analistas'  => Analyst::orderBy('name')->get(),
            'servicos'   => Service::orderBy('name')->get(),
        ]);
    }

    /* ============================================================
     * ADMIN → ENCAMINHAMENTO
     * ============================================================ */
    public function forward(Request $request, Contact $contact)
    {
        $request->validate([
            'analyst_id' => 'nullable|exists:analysts,id',
            'service_id' => 'nullable|exists:services,id',
        ]);

        /**
         * ENVIO PARA ANALISTA
         * - Mantém status Deferido
         * - Some da lista de pendentes do admin
         * - Usuário continua vendo Deferido
         */
        if ($request->filled('analyst_id')) {
            $status = Status::where('name', 'Deferido')->firstOrFail();

            $contact->update([
                'analyst_id' => $request->analyst_id,
                'status_id'  => $status->id,
            ]);
        }

        /**
         * ENVIO PARA SERVIÇO
         * - Status muda para Em Execução
         */
        if ($request->filled('service_id')) {
            $status = Status::where('name', 'Em Execução')->firstOrFail();

            $contact->update([
                'service_id' => $request->service_id,
                'status_id'  => $status->id,
            ]);
        }

        return response()->json(['message' => 'Encaminhado com sucesso']);
    }

    /* ============================================================
     * ADMIN → ATUALIZA STATUS MANUALMENTE
     * ============================================================ */
    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $indeferido = Status::where('name', 'Indeferido')->first()->id;

        $request->validate([
            'status_id' => 'required|exists:statuses,id',
            'justificativa' => Rule::requiredIf($request->status_id == $indeferido),
        ]);

        $contact->update([
            'status_id' => $request->status_id,
            'justificativa' => $request->justificativa,
        ]);

        return back()->with('success', 'Status atualizado');
    }
}
