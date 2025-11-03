<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth; // Facade para autenticação

class ContactController extends Controller
{
    // Adicione o middleware 'auth' se ele não estiver nas suas rotas!


    public function index()
    {
        return view('pages.contact'); // <-- CORRIGIDO!
    }

    public function store(Request $request)
    {
        // O Auth::check() é redundante aqui se o middleware estiver no __construct, 
        // mas garante segurança.
        if (!Auth::check()) {
            // Este redirecionamento não deve acontecer se a rota estiver protegida
            return redirect()->route('login')->withErrors('Você precisa estar logado para enviar uma solicitação.');
        }

        // Obtém o objeto completo do usuário logado (confirmado: tem 'name' e 'email')
        $user = Auth::user(); 

        // 1. Validação dos campos do formulário
        $validated = $request->validate([
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'descricao' => 'required|string', // Validando 'descricao'
        ]);
        
        // 2. Combina os dados validados com os dados do usuário logado
        $dataToSave = array_merge($validated, [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name, // Puxando o nome
            'email_solicitante' => $user->email, // Puxando o email
            'status' => 'novo', // Define o status padrão
        ]);

        // 3. Salva no banco de dados
        Contact::create($dataToSave);

        return redirect()->route('contact')->with('success', 'Sua solicitação foi enviada com sucesso! Entraremos em contato em breve.');
    }

    // --- MÉTODOS DE ADMIN ---

    /**
     * Mostra a lista de mensagens de contato para o admin.
     */
    public function adminContactList()
    {
        // Pega das mais novas para as mais antigas
        $messages = Contact::latest()->get(); 
        return view('admin.contacts.index', compact('messages'));
    }

    /**
     * Atualiza o status de uma mensagem de contato.
     * O Laravel injeta o $contact automaticamente pela Rota (Route Model Binding)
     */
    public function adminContactUpdateStatus(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'status' => 'required|in:novo,visto,resolvendo,resolvido',
        ]);

        $contact->update($validated);

        return redirect()->route('admin.contacts.index')->with('success', 'Status da mensagem atualizado.');
    }
}