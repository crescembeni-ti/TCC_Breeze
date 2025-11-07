<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Status;
use App\Models\Bairro; 
use App\Models\Topico; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; 

class ContactController extends Controller
{
    /**
     * [MÉTODO DO SITE - INTACTO]
     * Mostra o formulário de contato e carrega bairros e tópicos.
     */
    public function index()
    {
        $bairros = Bairro::orderBy('nome', 'asc')->get();
        $topicos = Topico::orderBy('nome', 'asc')->get();
        return view('pages.contact', compact('bairros', 'topicos'));
    }

    /**
     * [MÉTODO DO SITE - INTACTO]
     * Salva a solicitação de contato vinda do SITE.
     */
    public function store(Request $request)
    {
        $user = Auth::user(); 
        
        $nomesDeBairrosValidos = Bairro::pluck('nome')->toArray();
        $nomesDeTopicosValidos = Topico::pluck('nome')->toArray(); 

        $validated = $request->validate([
            'topico' => [ 
                'required', 'string', 'max:255',
                Rule::in($nomesDeTopicosValidos) 
            ],
            'bairro' => [
                'required', 'string', 'max:255',
                Rule::in($nomesDeBairrosValidos) 
            ],
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'descricao' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);
        
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('solicitacoes', 'public');
        }
        
        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });

        $dataToSave = array_merge($validated, [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name, 
            'email_solicitante' => $user->email,
            'status_id' => $statusEmAnaliseId,
            'justificativa' => null, 
            'foto_path' => $fotoPath,
        ]);

        Contact::create($dataToSave);

        return redirect()->route('contact')->with('success', 'Sua solicitação foi enviada com sucesso! Ela já está "Em Análise".');
    }

    // --- MÉTODOS DE ADMIN (INTACTOS) ---
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


    // --- MÉTODOS DO USUÁRIO (INTACTOS) ---
    public function userRequestList()
    {
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        $myRequests = Auth::user()
                            ->contacts()
                            ->with('status') 
                            ->where('status_id', '!=', $statusCanceladoId) 
                            ->latest()
                            ->get();
        return view('pages.my-requests', compact('myRequests'));
    }

    public function cancelRequest(Request $request, Contact $contact)
    {
        if (Auth::id() !== $contact->user_id) {
            abort(403); 
        }

        $statusConcluidoId = Cache::remember('status_concluido_id', 3600, function () {
            return Status::where('name', 'Concluído')->firstOrFail()->id;
        });
        
        $statusCanceladoId = Cache::remember('status_cancelado_id', 3600, function () {
            return Status::where('name', 'Cancelado')->firstOrFail()->id;
        });

        if ($contact->status_id === $statusConcluidoId || $contact->status_id === $statusCanceladoId) {
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

    
    // ===================================================================
    // ===================================================================
    //
    //    ADICIONE ESTE NOVO MÉTODO NO FINAL DO SEU ARQUIVO
    //
    // ===================================================================
    // ===================================================================

    /**
     * [NOVO MÉTODO - APENAS PARA API ANDROID]
     * Salva a solicitação de contato vinda da API (Android).
     */
    public function storeApi(Request $request)
    {
        // 1. Pega o usuário autenticado pela API (via token)
        $user = $request->user(); 

        // 2. Validação (Valida os nomes que o ANDROID envia)
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',    // Recebe 'titulo' do app
            'descricao' => 'required|string',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Recebe 'imagem' do app
        ]);

        $fotoPath = null; // O nome da sua coluna no BD é 'foto_path'

        // 3. Processar e salvar a imagem (se ela foi enviada)
        if ($request->hasFile('imagem')) { // O nome 'imagem' vem do Android
            // Salva a imagem
            $fotoPath = $request->file('imagem')->store('solicitacoes', 'public');
        }

        // 4. Busca o ID do status "Em Análise"
        $statusEmAnaliseId = Cache::remember('status_em_analise_id', 3600, function () {
            return Status::where('name', 'Em Análise')->firstOrFail()->id;
        });

        // 5. COMBINA E "TRADUZ" OS DADOS para salvar no banco
        $dataToSave = [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name,
            'email_solicitante' => $user->email,
            
            // --- A "TRADUÇÃO" ---
            'topico' => $validated['titulo'],     // Coluna 'topico' (BD) recebe 'titulo' (APP)
            'descricao' => $validated['descricao'],
            'foto_path' => $fotoPath,           // Coluna 'foto_path' (BD) recebe o $fotoPath
            
            'status_id' => $statusEmAnaliseId,
            'justificativa' => null,

            // Campos que o app não envia, mas o site sim (definidos como nulos)
            'bairro' => null, 
            'rua' => null,
            'numero' => null,
        ];
        
        // 6. Salva no banco de dados usando o Model 'Contact'
        $contact = Contact::create($dataToSave); 

        // 7. Retornar uma resposta de sucesso para o Android (JSON)
        return response()->json([
            'message' => 'Solicitação criada com sucesso!',
            'data' => $contact 
        ], 201); // 201 = "Created"
    }
}