<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth; // Facade para autenticação

class ContactController extends Controller
{
    /**
     * Mostra o formulário de contato.
     * O middleware 'auth' e 'verified' na rota já protegeu esta página.
     */
    public function index()
    {
        return view('pages.contact');
    }

    /**
     * Salva a solicitação de contato.
     * O middleware 'auth' e 'verified' na rota já protegeu esta ação.
     */
    public function store(Request $request)
    {
        // [REMOVIDO]
        // Não precisamos mais do 'if (!Auth::check())' aqui,
        // pois o middleware na rota já faz esse trabalho.

        // Obtém o objeto completo do usuário logado
        $user = Auth::user(); 

        // 1. Validação dos campos do formulário
        $validated = $request->validate([
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'descricao' => 'required|string',
        ]);
        
        // 2. Combina os dados validados com os dados do usuário logado
        $dataToSave = array_merge($validated, [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name, 
            'email_solicitante' => $user->email,
            'status' => 'novo',
        ]);

        // 3. Salva no banco de dados
        Contact::create($dataToSave);

        return redirect()->route('contact')->with('success', 'Sua solicitação foi enviada com sucesso! Entraremos em contato em breve.');
    }

    // --- MÉTODOS DE ADMIN (Estão corretos) ---

    public function adminContactList()
    {
        $messages = Contact::latest()->get(); 
        return view('admin.contacts.index', compact('messages'));
    }

    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'status' => 'required|in:novo,visto,resolvendo,resolvido',
        ]);

        $contact->update($validated);

        return redirect()->route('admin.contacts.index')->with('success', 'Status da mensagem atualizado.');
    }
}