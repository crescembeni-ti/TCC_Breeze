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
     * DADOS DO MAPA (JSON)
     * ============================================================ */
    public function getTreesData()
    {
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
            'registered_by' => $tree->admin ? $tree->admin->name : 'Sistema',
        ]);
    }

    /* ============================================================
     * VISUALIZAÇÃO (DETALHES)
     * ============================================================ */
    public function show($id)
    {
        $tree = Tree::with(['species', 'activities.user', 'admin'])->findOrFail($id);
        return view('trees.show', compact('tree'));
    }

    /* ============================================================
     * DASHBOARD ADMIN
     * ============================================================ */
    public function adminDashboard(Request $request)
    {
        $stats = [
            'total_trees' => Tree::count(),
            'total_activities' => Activity::count(),
            'total_species' => Species::count(),
        ];

        $query = AdminLog::with('admin')->latest();

        if ($request->has('filter') && $request->filter != '') {
            $filter = $request->filter;
            if ($filter == 'cadastro') {
                $query->where('action', 'like', '%create%');
            } elseif ($filter == 'atualizacao') {
                $query->where('action', 'like', '%update%');
            } elseif ($filter == 'exclusao') {
                $query->where('action', 'like', '%delete%');
            }
        }

        $adminLogs = $query->paginate(10)->appends($request->all());

        return view('admin.dashboard', compact('stats', 'adminLogs'));
    }

    /* ============================================================
     * MAPA ADMIN (CARREGA A VIEW DE CADASTRO)
     * ============================================================ */
    public function adminMap()
    {
        return view('admin.trees.map', [
            // ENVIA AS ESPÉCIES PARA O COMBOBOX DO ALPINE.JS
            'species' => Species::orderBy('name')->select('id', 'name')->get(), 
            
            'trees' => Tree::with(['species', 'bairro'])->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
        ]);
    }

    /* ============================================================
     * CADASTRAR ÁRVORE (LÓGICA HÍBRIDA: ID OU NOME)
     * ============================================================ */
    public function storeTree(Request $request)
    {
        // 1. Validação Adaptada
        $validated = $request->validate([
            // LÓGICA HÍBRIDA:
            'species_id' => 'nullable|exists:species,id', 
            // Se species_id for null, species_name é obrigatório
            'species_name' => 'nullable|nullable|string|max:255', 

            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'health_status' => 'nullable|in:good,fair,poor',
            'planted_at' => 'nullable|date|before_or_equal:today',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
            'vulgar_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'cap' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'crown_height' => 'nullable|numeric|min:0',
            'crown_diameter_longitudinal' => 'nullable|numeric|min:0',
            'crown_diameter_perpendicular' => 'nullable|numeric|min:0',
            'bifurcation_type' => 'nullable|string|max:100',
            'stem_balance' => 'nullable|string|max:100',
            'crown_balance' => 'nullable|string|max:100',
            'organisms' => 'nullable|string|max:255',
            'target' => 'nullable|string|max:255',
            'injuries' => 'nullable|string|max:255',
            'wiring_status' => 'nullable|string|max:100',
            'total_width' => 'nullable|numeric|min:0',
            'street_width' => 'nullable|numeric|min:0',
            'gutter_height' => 'nullable|numeric|min:0',
            'gutter_width' => 'nullable|numeric|min:0',
            'gutter_length' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        // 2. Determinar o ID da Espécie
        $speciesId = $request->species_id;
        $speciesNameLog = '';

        // Se NÃO veio ID, significa que é uma nova espécie ou o usuário digitou o nome
        if (!$speciesId) {
            $nameInput = trim($request->species_name);
            
            // Tenta achar pelo nome ou cria nova
            $species = Species::firstOrCreate(
                ['name' => $nameInput], 
                [
                    // Usa os dados do form para popular a nova espécie
                    'vulgar_name' => $request->vulgar_name, 
                    'scientific_name' => $request->scientific_name,
                    'color_code' => '#' . substr(md5($nameInput), 0, 6), // Cor aleatória
                    'description' => 'Cadastrada automaticamente pelo mapa.',
                ]
            );
            $speciesId = $species->id;
            $speciesNameLog = $species->name;
        } else {
            // Se veio ID, apenas busca o nome para o log
            $speciesNameLog = Species::find($speciesId)->name;
        }

        // 3. Prepara os dados da Árvore
        // Remove campos auxiliares que não vão na tabela trees (se houver)
        $treeData = collect($validated)
            ->except(['species_name', 'species_id']) // Remove campos de controle
            ->toArray();
        
        $treeData['species_id'] = $speciesId; // Insere o ID decidido acima
        $treeData['admin_id'] = auth('admin')->id();

        // 4. Salva a Árvore
        $tree = Tree::create($treeData);

        // 5. Gera Log
        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'create_tree',
            'description' => 'Árvore criada (ID ' . $tree->id . ') - Espécie: ' . $speciesNameLog,
        ]);

        return redirect()->route('admin.map')->with('success', 'Árvore cadastrada com sucesso!');
    }

    /* ============================================================
     * LISTA
     * ============================================================ */
    public function adminTreeList()
    {
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
     * ATUALIZAR
     * ============================================================ */
    /* ============================================================
     * ATUALIZAR (COM LÓGICA HÍBRIDA)
     * ============================================================ */
    public function adminTreeUpdate(Request $request, Tree $tree)
    {
        // 1. Validação Adaptada (Igual ao Store)
        $validated = $request->validate([
            // Lógica Híbrida: Aceita ID ou Nome
            'species_id' => 'nullable|exists:species,id',
            'species_name' => 'nullable|nullable|string|max:255',

            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'health_status' => 'nullable|in:good,fair,poor',
            'planted_at' => 'nullable|date|before_or_equal:today',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
            'vulgar_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'cap' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'crown_height' => 'nullable|numeric|min:0',
            'crown_diameter_longitudinal' => 'nullable|numeric|min:0',
            'crown_diameter_perpendicular' => 'nullable|numeric|min:0',
            'bifurcation_type' => 'nullable|string|max:100',
            'stem_balance' => 'nullable|string|max:100',
            'crown_balance' => 'nullable|string|max:100',
            'organisms' => 'nullable|string|max:255',
            'target' => 'nullable|string|max:255',
            'injuries' => 'nullable|string|max:255',
            'wiring_status' => 'nullable|string|max:100',
            'total_width' => 'nullable|numeric|min:0',
            'street_width' => 'nullable|numeric|min:0',
            'gutter_height' => 'nullable|numeric|min:0',
            'gutter_width' => 'nullable|numeric|min:0',
            'gutter_length' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        // 2. Determinar o ID da Espécie
        $speciesId = $request->species_id;
        $speciesNameLog = '';

        // Se NÃO veio ID, significa que o usuário digitou um nome novo ou diferente
        if (!$speciesId) {
            $nameInput = trim($request->species_name);
            
            // Tenta achar pelo nome ou cria nova
            $species = Species::firstOrCreate(
                ['name' => $nameInput], 
                [
                    'vulgar_name' => $request->vulgar_name, 
                    'scientific_name' => $request->scientific_name,
                    'color_code' => '#' . substr(md5($nameInput), 0, 6),
                    'description' => 'Cadastrada automaticamente via edição.',
                ]
            );
            $speciesId = $species->id;
            $speciesNameLog = $species->name;
        } else {
            $speciesNameLog = Species::find($speciesId)->name;
        }

        // 3. Atualizar a Árvore
        // Removemos campos auxiliares
        $updateData = collect($validated)
            ->except(['species_name', 'species_id'])
            ->toArray();
        
        $updateData['species_id'] = $speciesId;

        $tree->update($updateData);

        // 4. Log
        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'update_tree',
            'description' => 'Árvore atualizada (ID ' . $tree->id . ') - Espécie definida: ' . $speciesNameLog,
        ]);

        return redirect()->route('admin.trees.index')
            ->with('success', 'Árvore atualizada com sucesso!');
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