<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AboutPage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // Adicionado para inicialização, se necessário

class AboutPageController extends Controller
{
    /**
     * Define a estrutura inicial dos blocos de conteúdo para a página 'Sobre'.
     * Isso é usado para o firstOrCreate.
     * @return array
     */
    private function initialContentBlocks()
    {
        // Estrutura JSON inicial que simula o conteúdo estático original,
        // mas agora como blocos para o editor.
        return [
            [
                'id' => 1, 
                'type' => 'text', 
                'data' => [
                    'html' => '<p>Árvores de Paracambi é uma iniciativa dedicada ao mapeamento e preservação do patrimônio arbóreo da cidade de Paracambi, localizada no estado do Rio de Janeiro. Este projeto tem como objetivo principal criar um inventário completo das árvores urbanas do município, permitindo que cidadãos, gestores públicos e pesquisadores acompanhem a saúde e o desenvolvimento da floresta urbana local.</p>'
                ]
            ],
            [
                'id' => 2, 
                'type' => 'text', 
                'data' => [
                    'html' => '<h3>Nossa Missão</h3><p>Nossa missão é promover a conscientização ambiental e facilitar a gestão sustentável das Árvores Urbanas de Paracambi. Através deste mapa interativo, buscamos engajar a comunidade local no cuidado e na preservação das árvores...</p>'
                ]
            ],
            [
                'id' => 3, 
                'type' => 'youtube', 
                'data' => [
                    'url' => 'dQw4w9WgXcQ', // Placeholder de vídeo
                    'title' => 'Como Plantar Árvores (Título Editável)'
                ]
            ],
            [
                'id' => 4, 
                'type' => 'text', 
                'data' => [
                    'html' => '<h3>Benefícios das Árvores Urbanas</h3><ul><li>Qualidade do Ar: Filtram poluentes...</li><li>Conforto Térmico: Reduzem a temperatura...</li></ul>'
                ]
            ]
        ];
    }
    
    /**
     * Busca o conteúdo da página ou o inicializa com a estrutura de blocos.
     * @return AboutPage
     */
    private function getPageContent()
    {
        return AboutPage::firstOrCreate(
            ['id' => 1], 
            [
                'title' => 'Sobre o Projeto', 
                'content_blocks' => $this->initialContentBlocks() // Salva o array de blocos
            ]
        );
    }

    // [PÚBLICO] Mostra a página "Sobre" para todos (Modo Visualização)
    public function index()
    {
        $pageContent = $this->getPageContent();
        
        // Passa a flag 'isEditing' como FALSE
        return view('pages.about', ['pageContent' => $pageContent, 'isEditing' => false]);
    }

    // [ADMIN] Exibe a página "Sobre" no modo de edição in-place
    public function edit()
    {
        $pageContent = $this->getPageContent();
        
        // Passa a flag 'isEditing' como TRUE e usa a mesma view
        return view('pages.about', ['pageContent' => $pageContent, 'isEditing' => true]);
    }

    // [ADMIN] Salva o conteúdo editado via requisição AJAX (PUT)
    public function update(Request $request)
    {
        // Validação que o título e o JSON de blocos foram enviados
        $request->validate([
            'title' => 'required|string|max:255',
            'content_blocks_json' => 'required|json', // Espera a string JSON do front-end
        ]);

        $page = AboutPage::findOrFail(1); // Assume que ID 1 é a página 'Sobre'
        
        $page->update([
            'title' => $request->title,
            // Salva a string JSON. O Model (via $casts) cuida da conversão para array/JSON no BD.
            'content_blocks' => $request->content_blocks_json, 
        ]);

        // Retorna uma resposta JSON para o AJAX do front-end
        return response()->json([
            'success' => true, 
            'message' => 'Página "Sobre o Projeto" atualizada com sucesso!'
        ]);
    }
}