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
     * CADASTRAR ÁRVORE (COM ADMIN RESPONSÁVEL)
     * ============================================================ */
    public function storeTree(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

            // espécie
            'species_id' => 'nullable|exists:species,id',
            'new_species_name' => 'nullable|string|max:255',

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

        // Resolve a espécie (Existente ou Nova)
        if ($validated['species_id']) {
            $speciesId = $validated['species_id'];
        } else {
            $species = Species::firstOrCreate(
                ['name' => $validated['new_species_name']],
                [
                    'color_code' => '#' . substr(md5($validated['new_species_name']), 0, 6),
                    'description' => 'Cadastrada via mapa admin',
                ]
            );
            $speciesId = $species->id;
        }

        // Prepara os dados para salvar
        $treeData = collect($validated)
            ->except(['species_id', 'new_species_name']) // Remove campos auxiliares
            ->toArray();
        
        // Adiciona IDs vinculados
        $treeData['species_id'] = $speciesId;
        
        // === AQUI ESTÁ A MUDANÇA: SALVA QUEM CADASTROU ===
        $treeData['admin_id'] = auth('admin')->id(); 
        // =================================================

        // Cria a árvore no banco
        $tree = Tree::create($treeData);

        // Gera Log de atividade
        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'create_tree',
            'description' => 'Árvore criada (ID ' . $tree->id . ')',
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