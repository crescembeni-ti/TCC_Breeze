<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutPage;

class AboutPageController extends Controller
{
    /**
     * Garante que o registro da página existe no banco.
     * Se não existir, cria um padrão.
     */
    private function getPageContent()
    {
        return AboutPage::firstOrCreate(
            ['id' => 1], 
            [
                'title' => 'Sobre o Projeto', 
                'content' => '<p>Texto inicial...</p>',
                // Os outros campos serão criados como null
            ]
        );
    }

    // ======================================================
    //  ÁREA PÚBLICA (O Site que todo mundo vê)
    // ======================================================
    public function index()
    {
        $pageContent = $this->getPageContent();
        
        // Retorna a visualização bonita (Página Verde)
        return view('pages.about', ['pageContent' => $pageContent]);
    }

    // ======================================================
    //  ÁREA DO ADMINISTRADOR (Edição)
    // ======================================================
    
    // 1. Exibe o formulário com Summernote
    public function edit()
    {
        $pageContent = $this->getPageContent();
        
        // CORREÇÃO CRUCIAL:
        // Aponta para a pasta onde criamos o formulário novo
        return view('admin.about.edit', ['pageContent' => $pageContent]);
    }

    // 2. Salva as alterações no banco de dados
    public function update(Request $request)
    {
        // Valida os campos de texto simples (não mais JSON)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'mission_content' => 'nullable|string',
            'how_it_works_content' => 'nullable|string',
            'benefits_content' => 'nullable|string',
        ]);

        $page = AboutPage::findOrFail(1);
        
        // Atualiza as colunas
        $page->update($validated);

        // Retorna para o formulário com mensagem de sucesso
        return back()->with('success', 'Página "Sobre o Projeto" atualizada com sucesso!');
    }

    // ======================================================
    //  API (Para o seu Aplicativo Android)
    // ======================================================
    public function apiIndex()
    {
        $page = $this->getPageContent();

        // Retorna JSON limpo para o App montar a tela nativa
        return response()->json([
            'title' => $page->title,
            'sections' => [
                [
                    'title' => 'Introdução',
                    'content' => $page->content
                ],
                [
                    'title' => 'Nossa Missão',
                    'content' => $page->mission_content
                ],
                [
                    'title' => 'Como Funciona',
                    'content' => $page->how_it_works_content
                ],
                [
                    'title' => 'Benefícios',
                    'content' => $page->benefits_content
                ]
            ],
            'updated_at' => $page->updated_at
        ]);
    }
}