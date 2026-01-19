<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutPage;
use Illuminate\Support\Facades\Storage; 

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
                'sections' => [] // Inicializa vazio
            ]
        );
    }

    // ======================================================
    //  ÁREA PÚBLICA (O Site que todo mundo vê)
    // ======================================================
    public function index()
    {
        $pageContent = $this->getPageContent();
        return view('pages.about', ['pageContent' => $pageContent]);
    }

    // ======================================================
    //  ÁREA DO ADMINISTRADOR (Edição)
    // ======================================================
    
    // 1. Exibe o formulário com Summernote
    public function edit()
    {
        $pageContent = $this->getPageContent();
        return view('admin.about.edit', ['pageContent' => $pageContent]);
    }

    // 2. Salva as alterações no banco de dados (COM SEÇÕES DINÂMICAS)
    public function update(Request $request)
    {
        // Validação
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            
            // Validação das seções dinâmicas
            'sections' => 'nullable|array',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.content' => 'nullable|string',
        ]);

        $page = AboutPage::findOrFail(1);
        
        $data = $validated;
        
        // Se não vier nenhuma seção (usuário apagou tudo), salva array vazio
        if (!isset($data['sections'])) {
            $data['sections'] = [];
        }

        // Reindexa o array para garantir que seja sequencial (0, 1, 2...) no JSON
        $data['sections'] = array_values($data['sections']);

        // Atualiza no banco
        $page->update($data);

        return back()->with('success', 'Página atualizada com sucesso!');
    }

    // 3. Upload de Vídeo via AJAX (Summernote)
    public function uploadVideo(Request $request)
    {
        if ($request->hasFile('video')) {
            $request->validate([
                'video' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:51200', 
            ]);

            $path = $request->file('video')->store('videos', 'public');
            
            return response()->json(['url' => Storage::url($path)]);
        }

        return response()->json(['error' => 'Nenhum arquivo enviado'], 400);
    }

    // ======================================================
    //  API (Para o seu Aplicativo Android)
    // ======================================================
    public function apiIndex()
    {
        $page = $this->getPageContent();

        // Monta a estrutura para o App
        // A Introdução é fixa, o resto vem do array dinâmico
        $sections = [
            [
                'title' => 'Introdução',
                'content' => $page->content
            ]
        ];

        // Adiciona as seções dinâmicas se existirem
        if (!empty($page->sections) && is_array($page->sections)) {
            foreach ($page->sections as $section) {
                $sections[] = [
                    'title' => $section['title'],
                    'content' => $section['content']
                ];
            }
        }

        return response()->json([
            'title' => $page->title,
            'sections' => $sections,
            'updated_at' => $page->updated_at
        ]);
    }
}