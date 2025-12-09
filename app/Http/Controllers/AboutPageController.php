<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AboutPage;
use Illuminate\Support\Facades\Storage;

class AboutPageController extends Controller
{
    // [PÚBLICO] Mostra a página "Sobre" para todos
    public function index()
    {
        // Pega a primeira (e única) entrada
        $pageContent = AboutPage::firstOrCreate(['id' => 1], ['title' => 'Árvores de Paracambi', 'content' => 'Conteúdo inicial...']);
        return view('pages.about', compact('pageContent'));
    }

    // [ADMIN] Exibe o formulário de edição
    public function edit()
    {
        $pageContent = AboutPage::firstOrCreate(['id' => 1], ['title' => 'Árvores de Paracambi', 'content' => 'Conteúdo inicial...']);
        return view('admin.about.edit', compact('pageContent'));
    }

    // [ADMIN] Salva o conteúdo editado
    public function update(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'mission_content' => 'nullable|string',
            'how_it_works_content' => 'nullable|string',
            'benefits_content' => 'nullable|string',
        ]);

        $page = AboutPage::findOrFail(1); // Assume que ID 1 é a página 'Sobre'
        $page->update($validated);

        return redirect()->route('admin.about.edit')
                         ->with('success', 'Página "Sobre o Projeto" atualizada com sucesso!');
    }
}