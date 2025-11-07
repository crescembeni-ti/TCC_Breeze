<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // <-- 1. IMPORTAR O STORAGE

class ContactController extends Controller
{
    /**
     * Mostra o formulário de contato e carrega os bairros.
     */
    public function index()
    {
        // 2. BUSCA OS BAIRROS DO BANCO
        $bairros = Bairro::orderBy('nome', 'asc')->get();

        // 3. ENVIA A VARIÁVEL $bairros PARA A VIEW
        return view('pages.contact', compact('bairros'));
    }

    /**
     * Salva a solicitação de contato.
     * (AJUSTADO PARA VALIDAR E SALVAR A FOTO)
     */
    public function store(Request $request)
    {
        $user = Auth::user(); 

        $nomesDeBairrosValidos = Bairro::pluck('nome')->toArray();

        // 2. VALIDAÇÃO ATUALIZADA (COM 'foto')
        $validated = $request->validate([
            'bairro' => [
                'required',
                'string',
                'max:255',
                Rule::in($nomesDeBairrosValidos) 
            ],
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'descricao' => 'required|string',
            // Validação da foto: opcional, deve ser imagem, tipos, max 2MB
            'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048', // <-- 2. ADICIONADA VALIDAÇÃO DA FOTO
        ]);
        
        // --- 3. LÓGICA PARA PROCESSAR A FOTO ---
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            // Salva o arquivo em 'storage/app/public/solicitacoes'
            // O 'public' indica o disco. A pasta 'solicitacoes' será criada.
            $fotoPath = $request->file('foto')->store('solicitacoes', 'public');
        }
        // --- FIM DA LÓGICA DA FOTO ---

        
        // Busca o ID do status "Em Análise"
        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });

        // Combina os dados para salvar
        $dataToSave = array_merge($validated, [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name, 
            'email_solicitante' => $user->email,
            'status_id' => $statusEmAnaliseId,
            'justificativa' => null, 
            'foto_path' => $fotoPath, // <-- 4. ADICIONADO O CAMINHO DA FOTO
        ]);

        // Salva no banco de dados
        Contact::create($dataToSave);

        return redirect()->route('contact')->with('success', 'Sua solicitação foi enviada com sucesso! Ela já está "Em Análise".');
    }

    // --- MÉTODOS DE ADMIN (Sem alterações) ---

    public function adminContactList()
    {
        $messages = Contact::with('status', 'user') 
                            ->latest()
                            ->get(); 

        $allStatuses = Status::all();

        return view('admin.contacts.index', compact('messages', 'allStatuses'));
    }

    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $statusIndeferidoId = Cache::remember('status_indeferido_id', 3600, function () {
            return Status::where('name', 'Indeferido')->firstOrFail()->id;
        });

        $validated = $request->validate([
            'status_id' => 'required|integer|exists:statuses,id',
            'justificativa' => [
                'nullable',
                'string',
                Rule::requiredIf($request->status_id == $statusIndeferidoId)
            ],
        ]);

        $dataToSave = [
            'status_id' => $validated['status_id'],
            'justificativa' => $validated['justificativa'],
        ];

        if ($validated['status_id'] != $statusIndeferidoId) {
            $dataToSave['justificativa'] = null;
        }

        $contact->update($dataToSave);

        return redirect()->route('admin.contacts.index')->with('success', 'Status da mensagem atualizado.');
    }

    // --- MÉTODOS DO USUÁRIO (Sem alterações) ---

    public function userRequestList()
    {
        $myRequests = Auth::user()
                            ->contacts()
                            ->with('status') 
                            ->latest()
                            ->get();

        return view('pages.my-requests', compact('myRequests'));
    }

    public function cancelRequest(Request $request, Contact $contact)
    {
        if (Auth::id() !== $contact->user_id) {
            abort(403);
        }

        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });
        
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        if ($contact->status_id !== $statusEmAnaliseId) {
            return back()->withErrors(['cancel_error' => 'Esta solicitação não pode mais ser cancelada.']);
        }

        $request->validate([
            'justificativa_cancelamento' => 'nullable|string|max:500'
        ]);

        $contact->update([
            'status_id' => $statusCanceladoId,
            'justificativa' => $request->justificativa_cancelamento 
                                    ? 'Cancelado pelo usuário: ' . $request->justificativa_cancelamento
                                    : 'Cancelado pelo usuário (sem motivo informado).'
        ]);

        return redirect()->route('contact.myrequests')->with('success', 'Solicitação cancelada com sucesso.');
    }
}