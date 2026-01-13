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
        return Tree::with(['species', 'bairro', 'admin'])
            ->where('aprovado', true) // <--- FILTRO: Só mostra as aprovadas
            ->get()
            ->map(fn ($tree) => [
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
            } elseif ($filter == 'aprovacao') {
                $query->where('action', 'approve_tree');
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
            'species' => Species::orderBy('name')->select('id', 'name')->get(), 
            'trees' => Tree::with(['species', 'bairro'])->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
        ]);
    }

   /* ============================================================
     * CADASTRAR ÁRVORE (LÓGICA HÍBRIDA + MODERAÇÃO)
     * ============================================================ */
    public function storeTree(Request $request)
    {
        // 1. Validação
        $validated = $request->validate([
            'species_id' => 'nullable|exists:species,id', 
            
            // CORREÇÃO AQUI: Troquei nullable_without por required_without
            'species_name' => 'required_without:species_id|nullable|string|max:255',
            
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

        // 2. Lógica da Espécie
        $speciesId = $request->species_id;
        $speciesNameLog = '';

        if (!$speciesId) {
            $nameInput = trim($request->species_name);
            $species = \App\Models\Species::firstOrCreate(
                ['name' => $nameInput], 
                [
                    'vulgar_name' => $request->vulgar_name, 
                    'scientific_name' => $request->scientific_name,
                    'color_code' => '#' . substr(md5($nameInput), 0, 6),
                    'description' => 'Cadastrada automaticamente pelo mapa.',
                ]
            );
            $speciesId = $species->id;
            $speciesNameLog = $species->name;
        } else {
            $speciesObj = \App\Models\Species::find($speciesId);
            $speciesNameLog = $speciesObj ? $speciesObj->name : 'Desconhecida';
        }

       // 3. Prepara os dados
        $treeData = collect($validated)
            ->except(['species_name', 'species_id']) 
            ->toArray();
        
        $treeData['species_id'] = $speciesId;
        
        // --- LÓGICA DE APROVAÇÃO ROBUSTA ---
        if (auth()->guard('analyst')->check()) {
            $treeData['admin_id'] = null;
            $treeData['analyst_id'] = auth()->guard('analyst')->id();
            $treeData['aprovado'] = 0; // Analista = Pendente (0)
        } 
        elseif (auth()->guard('admin')->check()) {
            $treeData['admin_id'] = auth()->guard('admin')->id();
            $treeData['analyst_id'] = null;
            $treeData['aprovado'] = 1; // Admin = Aprovado (1)
        } else {
            $treeData['aprovado'] = 0; // Segurança
        }

        // 4. Salva a Árvore
        $tree = Tree::create($treeData);
        
        // Verifica se salvou errado e corrige na força bruta (Debug de Segurança)
        if (auth()->guard('analyst')->check() && $tree->aprovado == 1) {
            $tree->aprovado = 0;
            $tree->save();
        }

        // 5. Gera Log (Apenas Admin gera log de admin_logs)
        if (auth()->guard('admin')->check()) {
            \App\Models\AdminLog::create([
                'admin_id' => auth()->guard('admin')->id(),
                'action' => 'create_tree',
                'description' => 'Árvore criada (ID ' . $tree->id . ') - Espécie: ' . $speciesNameLog,
            ]);
        }

        // 6. Redirecionamento
        if (auth()->guard('analyst')->check()) {
            return redirect()->route('analyst.map')->with('success', 'Árvore enviada para aprovação do Administrador!');
        }

        return redirect()->route('admin.map')->with('success', 'Árvore cadastrada e publicada com sucesso!');
    }

    /* ============================================================
     * LISTA AS PENDENTES
     * ============================================================ */
    public function pendingTrees()
    {
        // Busca árvores onde aprovado é false (0)
        $pendingTrees = Tree::where('aprovado', false)->with('species')->get();
        return view('admin.trees.pending', compact('pendingTrees'));
    }

    /* ============================================================
     * AÇÃO DE APROVAR
     * ============================================================ */
    public function approveTree($id)
    {
        $tree = Tree::findOrFail($id);
        $tree->update(['aprovado' => true]);

        if (auth()->guard('admin')->check()) {
            \App\Models\AdminLog::create([
                'admin_id' => auth()->guard('admin')->id(),
                'action' => 'approve_tree',
                'description' => 'Aprovou a árvore ID ' . $tree->id,
            ]);
        }

        return back()->with('success', 'Árvore aprovada e publicada no mapa!');
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
     * ATUALIZAR (COM LÓGICA HÍBRIDA)
     * ============================================================ */
    public function adminTreeUpdate(Request $request, Tree $tree)
    {
        // 1. Validação Adaptada
        $validated = $request->validate([
            'species_id' => 'nullable|exists:species,id',
            
            // CORREÇÃO AQUI TAMBÉM: required_without
            'species_name' => 'required_without:species_id|nullable|string|max:255',

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

        if (!$speciesId) {
            $nameInput = trim($request->species_name);
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

    // ==========================================================
    // ÁREA DO ANALISTA
    // ==========================================================

    public function analystMap()
    {
        $bairros = Bairro::orderBy('nome')->get();
        $species = Species::orderBy('name')->get();
        $trees = Tree::with('species')->get(); 

        return view('analista.map', compact('bairros', 'species', 'trees'));
    }
    
    public function analystTreeList()
    {
        $trees = Tree::all(); 
        return view('analista.trees.index', compact('trees'));
    }
}