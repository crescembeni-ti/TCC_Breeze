<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;

class ContatoApiController extends Controller
{
    /**
     * Armazena uma nova solicitação de contato vinda do app.
     */
    public function store(Request $request)
    {
        // Pega o usuário que está fazendo a chamada (via Token)
        $user = Auth::user(); 

        $validated = $request->validate([
            'bairro' => 'required|string|max:255',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'descricao' => 'required|string',
        ]);

        $dataToSave = array_merge($validated, [
            'user_id' => $user->id, 
            'nome_solicitante' => $user->name, 
            'email_solicitante' => $user->email,
            'status' => 'novo', // Status padrão
        ]);

        $contact = Contact::create($dataToSave);

        // Retorna o contato criado como JSON
        return response()->json($contact, 201); // 201 = "Criado"
    }
}