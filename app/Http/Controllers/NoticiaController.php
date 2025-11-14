<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia; // Importe o modelo

class NoticiaController extends Controller
{
    /**
     * Mostra o formulário para criar uma nova notícia.
     */
    public function create()
    {
        // Apenas retorna a view do formulário
        return view('noticias.create'); // Vamos criar este arquivo
    }

    /**
     * Salva a nova notícia no banco de dados.
     */
    public function store(Request $request)
    {
        // 1. Valida os dados
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'conteudo' => 'required|string',
            'imagem_url' => 'nullable|url' // (Opcional: valida se é uma URL)
        ]);

        // 2. Cria a notícia no banco
        Noticia::create($validated);

        // 3. Redireciona de volta com uma mensagem de sucesso
        return redirect()->route('dashboard')->with('success', 'Notícia cadastrada com sucesso!');
        // (Mude 'dashboard' para a sua página principal de admin)
    }
    
    // (Opcional: Método para mostrar a lista de notícias no admin)
    public function index()
    {
        $noticias = Noticia::latest()->get();
        // return view('noticias.index', compact('noticias'));
    }
}