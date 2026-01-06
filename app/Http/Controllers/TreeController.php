<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\Species;
use App\Models\Activity;
use App\Models\AdminLog;
use App\Models\Bairro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class TreeController extends Controller
{
    /* ============================================================
     * PÁGINA PÚBLICA
     * ============================================================ */
    public function index()
    {
        $stats = [
            'total_trees' => Tree::count(),
            'total_activities' => Activity::count(),
            'total_species' => Species::count(),
        ];

        $recentActivities = Activity::with(['tree.species', 'user'])
            ->orderBy('activity_date', 'desc')
            ->take(5)
            ->get();

        $bairros = Bairro::orderBy('nome')->get();

        return view('welcome', compact('stats', 'recentActivities', 'bairros'));
    }

    /* ============================================================
     * DADOS DO MAPA
     * ============================================================ */
    public function getTreesData()
    {
        // Adicionei 'admin' no with() caso queira mostrar no mapa quem cadastrou
        return Tree::with(['species', 'bairro', 'admin'])->get()->map(fn ($tree) => [
            'id' => $tree->id,
            'latitude' => (float) $tree->latitude,
            'longitude' => (float) $tree->longitude,
            'species_name' => $tree->species->name,
            'color_code' => $tree->species->color_code,
            'address' => $tree->address,
            'bairro_id' => $tree->bairro_id,
            'bairro_nome' => $tree->bairro->nome ?? null,
            'health_status' => $tree->health_status,
            'trunk_diameter' => $tree->trunk_diameter,
            // Opcional: retornar nome do admin para o mapa
            'registered_by' => $tree->admin ? $tree->admin->name : 'Sistema',
        ]);
    }

    /* ============================================================
     * VISUALIZAÇÃO
     * ============================================================ */
    public function show($id)
    {
        // Carrega também o relacionamento 'admin' para mostrar na tela
        $tree = Tree::with(['species', 'activities.user', 'admin'])->findOrFail($id);
        return view('trees.show', compact('tree'));
    }

    /* ============================================================
     * DASHBOARD ADMIN (COM FILTRO DE LOGS)
     * ============================================================ */
    public function adminDashboard(Request $request)
    {
        // 1. Estatísticas (Mantém igual)
        $stats = [
            'total_trees' => Tree::count(),
            'total_activities' => Activity::count(),
            'total_species' => Species::count(),
        ];

        // 2. Query dos Logs
        $query = AdminLog::with('admin')->latest();

        // 3. Aplica o Filtro se houver
        if ($request->has('filter') && $request->filter != '') {
            $filter = $request->filter;

            if ($filter == 'cadastro') {
                // Busca ações que tenham "create" no nome (ex: create_tree)
                $query->where('action', 'like', '%create%');
            } elseif ($filter == 'atualizacao') {
                // Busca ações que tenham "update"
                $query->where('action', 'like', '%update%');
            } elseif ($filter == 'exclusao') {
                // Busca ações que tenham "delete"
                $query->where('action', 'like', '%delete%');
            }
        }

        // 4. Paginação (Pega 10 por página e mantém o filtro na URL ao mudar de página)
        $adminLogs = $query->paginate(10)->appends($request->all());

        return view('admin.dashboard', compact('stats', 'adminLogs'));
    }

    /* ============================================================
     * MAPA ADMIN
     * ============================================================ */
    public function adminMap()
    {
        return view('admin.trees.map', [
            'species' => Species::orderBy('name')->get(),
            'trees' => Tree::with(['species', 'bairro'])->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
        ]);
    }

  /* ============================================================
     * CADASTRAR ÁRVORE (AUTO-DETECTAR OU CRIAR ESPÉCIE)
     * ============================================================ */
    public function storeTree(Request $request)
    {
        // 1. Validação (Agora validamos o TEXTO do nome, não o ID)
        $validated = $request->validate([
            // Campo de texto simples para o nome da espécie
            'species_name' => 'required|string|max:255', 
            
            // Demais campos
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'health_status' => 'required|in:good,fair,poor',
            'planted_at' => 'required|date|before_or_equal:today',
            'trunk_diameter' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'bairro_id' => 'required|exists:bairros,id',
            'vulgar_name' => 'required|string|max:255',
            'scientific_name' => 'required|string|max:255',
            'cap' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'crown_height' => 'required|numeric|min:0',
            'crown_diameter_longitudinal' => 'required|numeric|min:0',
            'crown_diameter_perpendicular' => 'required|numeric|min:0',
            'bifurcation_type' => 'required|string|max:100',
            'stem_balance' => 'required|string|max:100',
            'crown_balance' => 'required|string|max:100',
            'organisms' => 'required|string|max:255',
            'target' => 'required|string|max:255',
            'injuries' => 'required|string|max:255',
            'wiring_status' => 'required|string|max:100',
            'total_width' => 'required|numeric|min:0',
            'street_width' => 'required|numeric|min:0',
            'gutter_height' => 'required|numeric|min:0',
            'gutter_width' => 'required|numeric|min:0',
            'gutter_length' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        // 2. Lógica Inteligente: Busca ou Cria a Espécie
        // Remove espaços extras do nome digitado
        $speciesName = trim($validated['species_name']);

        $species = Species::firstOrCreate(
            ['name' => $speciesName], // Procura por este nome
            [
                // Se não achar, cria com estes dados padrão:
                'vulgar_name' => $validated['vulgar_name'], // Aproveita o nome vulgar digitado
                'scientific_name' => $validated['scientific_name'], // Aproveita o científico
                // Gera uma cor aleatória baseada no nome para ficar bonito no mapa
                'color_code' => '#' . substr(md5($speciesName), 0, 6),
                'description' => 'Cadastrada automaticamente pelo mapa.',
            ]
        );

        // 3. Prepara os dados da Árvore
        // Remove o campo 'species_name' pois na tabela trees usamos 'species_id'
        $treeData = collect($validated)->except(['species_name'])->toArray();
        
        $treeData['species_id'] = $species->id; // Usa o ID (existente ou novo)
        $treeData['admin_id'] = auth('admin')->id(); // Vincula ao ADM logado

        // 4. Salva a Árvore
        $tree = Tree::create($treeData);

        // 5. Gera Log
        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'create_tree',
            'description' => 'Árvore criada (ID ' . $tree->id . ') - Espécie: ' . $species->name,
        ]);

        return redirect()->route('admin.map')->with('success', 'Árvore cadastrada com sucesso!');
    }

    /* ============================================================
     * LISTA
     * ============================================================ */
    public function adminTreeList()
    {
        // Carrega o 'admin' para mostrar na lista quem criou
        return view('admin.trees.index', [
            'trees' => Tree::with(['species', 'admin'])->latest()->get(),
        ]);
    }

    /* ============================================================
     * EDITAR
     * ============================================================ */
    public function adminTreeEdit(Tree $tree)
    {
        return view('admin.trees.edit', [
            'tree' => $tree,
            'species' => Species::orderBy('name')->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
        ]);
    }

    /* ============================================================
     * ATUALIZAR (TODOS OS CAMPOS)
     * ============================================================ */
    public function adminTreeUpdate(Request $request, Tree $tree)
    {
        $validated = $request->validate([
            'species_id' => 'required|exists:species,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'health_status' => 'required|in:good,fair,poor',
            'planted_at' => 'required|date|before_or_equal:today',
            'trunk_diameter' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'bairro_id' => 'required|exists:bairros,id',

            'vulgar_name' => 'required|string|max:255',
            'scientific_name' => 'required|string|max:255',
            'cap' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'crown_height' => 'required|numeric|min:0',
            'crown_diameter_longitudinal' => 'required|numeric|min:0',
            'crown_diameter_perpendicular' => 'required|numeric|min:0',
            'bifurcation_type' => 'required|string|max:100',
            'stem_balance' => 'required|string|max:100',
            'crown_balance' => 'required|string|max:100',
            'organisms' => 'required|string|max:255',
            'target' => 'required|string|max:255',
            'injuries' => 'required|string|max:255',
            'wiring_status' => 'required|string|max:100',
            'total_width' => 'required|numeric|min:0',
            'street_width' => 'required|numeric|min:0',
            'gutter_height' => 'required|numeric|min:0',
            'gutter_width' => 'required|numeric|min:0',
            'gutter_length' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $tree->update($validated);

        // OBS: Não atualizamos o admin_id aqui, pois o responsável
        // é quem CRIOU a árvore, não quem editou.
        // Quem editou fica salvo apenas no AdminLog abaixo.

        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'update_tree',
            'description' => 'Árvore atualizada (ID ' . $tree->id . ')',
        ]);

        return redirect()->route('admin.trees.index')
            ->with('success', 'Árvore atualizada!');
    }

    /* ============================================================
     * EXCLUIR
     * ============================================================ */
    public function adminTreeDestroy(Tree $tree)
    {
        $id = $tree->id;
        $tree->delete();

        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'delete_tree',
            'description' => "Árvore deletada (ID $id)",
        ]);

        return redirect()->route('admin.trees.index')
            ->with('success', 'Árvore excluída!');
    }
}