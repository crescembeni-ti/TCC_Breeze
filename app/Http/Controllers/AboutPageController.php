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
     * Se as seções estiverem vazias (por erro anterior), restaura o padrão.
     */
    private function getPageContent()
    {
        // Busca ou cria o registro inicial
        $page = AboutPage::firstOrCreate(
            ['id' => 1], 
            [
                'title' => 'Sobre o Projeto', 
                'content' => '<p>Árvores de Paracambi é uma iniciativa dedicada ao mapeamento e preservação do patrimônio arbóreo da cidade...</p>',
                'sections' => [] // Inicializa vazio se for novo
            ]
        );

        // --- RESTAURAÇÃO AUTOMÁTICA DE CONTEÚDO ---
        // Se a lista de seções estiver vazia (ex: apagou na migração ou erro de edição),
        // recria as seções padrão que você pediu.
        if (empty($page->sections)) {
            $defaultSections = [
                [
                    'title' => '🎯 Nossa Missão',
                    'content' => '<p>Promover a conscientização ambiental e fornecer ferramentas tecnológicas para a gestão eficiente da arborização urbana de Paracambi, conectando cidadãos e gestão pública em prol de uma cidade mais verde.</p>'
                ],
                [
                    'title' => '⚙️ Como Funciona',
                    'content' => '<p>O sistema permite que usuários cadastrados registrem árvores encontradas pela cidade, incluindo informações detalhadas como:</p><ul><li>Localização geográfica precisa (latitude e longitude)</li><li>Espécie da árvore (nome comum e científico)</li><li>Diâmetro do tronco e estado de saúde</li><li>Histórico de atividades de manutenção</li><li>Fotografias das árvores</li></ul>'
                ],
                [
                    'title' => '🌳 Benefícios das Árvores',
                    'content' => '<p>As árvores urbanas desempenham um papel crucial no ambiente urbano, proporcionando diversos benefícios:</p><ul><li><strong>Qualidade do Ar:</strong> Filtram poluentes e produzem oxigênio</li><li><strong>Conforto Térmico:</strong> Reduzem a temperatura ambiente através da sombra e evapotranspiração</li><li><strong>Gestão de Águas Pluviais:</strong> Interceptam a água da chuva, reduzindo o escoamento superficial</li><li><strong>Biodiversidade:</strong> Fornecem habitat para diversas espécies de fauna</li><li><strong>Bem-estar Social:</strong> Melhoram a estética urbana e proporcionam espaços de convivência</li></ul>'
                ]
            ];

            // Salva os padrões no banco
            $page->update(['sections' => $defaultSections]);
            
            // Recarrega o objeto do banco para garantir que a view receba os dados novos
            $page->refresh();
        }

        return $page;
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

    // 2. Salva as alterações no banco de dados
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
        
        // Se não vier nenhuma seção (usuário apagou tudo no front), 
        // define como array vazio para não dar erro.
        if (!isset($data['sections'])) {
            $data['sections'] = [];
        }

        // --- CORREÇÃO CRÍTICA ---
        // Reorganiza os índices do array (0, 1, 2...) para garantir que o Laravel
        // salve como um Array JSON `[...]` e não um Objeto JSON `{"0":..., "2":...}`.
        // Isso resolve o problema de "sumir" conteúdo ao editar/remover.
        $data['sections'] = array_values($data['sections']);

        $page->update($data);

        return back()->with('success', 'Página "Sobre" atualizada com sucesso!');
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