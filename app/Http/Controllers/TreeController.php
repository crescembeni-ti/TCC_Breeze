<?php

namespace App\Http\Controllers;

use App\Models\Tree;
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
            // Removemos total_species pois a tabela não existe mais
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'), 
        ];

        // Carrega atividades recentes (sem species)
        $recentActivities = Activity::with(['tree', 'user'])
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
        return Tree::with(['bairro', 'admin']) 
            ->where('aprovado', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->get()
            ->map(fn ($tree) => [
                'id' => $tree->id,
                'latitude' => (float) $tree->latitude,
                'longitude' => (float) $tree->longitude,
                
                // LÓGICA DE EXIBIÇÃO DO NOME (Direto da tabela trees)
                'species_name' => $tree->scientific_name 
                                  ?? $tree->vulgar_name 
                                  ?? 'Árvore Não Identificada',
                
                'color_code' => '#358054', // Cor padrão
                
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
        // Removemos 'species' do with
        $tree = Tree::with(['activities.user', 'admin'])->findOrFail($id);
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
            // Conta nomes científicos únicos na tabela trees
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'),
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
     * MAPA ADMIN
     * ============================================================ */
    /* ============================================================
     * MAPA ADMIN (CADASTRO)
     * ============================================================ */
    public function adminMap()
    {
        // Busca todos os nomes científicos distintos já cadastrados para o autocomplete
        $scientificNames = Tree::whereNotNull('scientific_name')
            ->where('scientific_name', '!=', '')
            ->distinct()
            ->orderBy('scientific_name')
            ->pluck('scientific_name');

        return view('admin.trees.map', [
            'trees' => Tree::with(['bairro'])->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
            'scientificNames' => $scientificNames, // Envia para a view
        ]);
    }

    /* ============================================================
     * CADASTRAR ÁRVORE (DIRETO NA TABELA TREES)
     * ============================================================ */
    public function storeTree(Request $request)
    {
        $validated = $request->validate([
            'scientific_name' => 'nullable|string|max:255',
            'vulgar_name' => 'nullable|string|max:255',
            
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'health_status' => 'nullable|in:good,fair,poor',
            'planted_at' => 'nullable|date|before_or_equal:today',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
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
            'no_species_case' => 'nullable|string|max:255',
        ]);

        // Removemos o campo auxiliar species_name se vier da view antiga
        $treeData = collect($validated)->except(['species_name'])->toArray();
        
        // --- LÓGICA DE APROVAÇÃO ---
        if (auth()->guard('analyst')->check()) {
            $treeData['admin_id'] = null;
            $treeData['analyst_id'] = auth()->guard('analyst')->id();
            $treeData['aprovado'] = 0; // Analista = Pendente
        } elseif (auth()->guard('admin')->check()) {
            $treeData['admin_id'] = auth()->guard('admin')->id();
            $treeData['analyst_id'] = null;
            $treeData['aprovado'] = 1; // Admin = Aprovado
        } else {
            $treeData['aprovado'] = 0;
        }

        $tree = Tree::create($treeData);

        if (auth()->guard('admin')->check()) {
            \App\Models\AdminLog::create([
                'admin_id' => auth()->guard('admin')->id(),
                'action' => 'create_tree',
                'description' => 'Árvore criada (ID ' . $tree->id . ') - Nome: ' . ($tree->scientific_name ?? 'S/ Nome'),
            ]);
        }

        if (auth()->guard('analyst')->check()) {
            return redirect()->route('analyst.map')->with('success', 'Enviado para aprovação!');
        }

        return redirect()->route('admin.map')->with('success', 'Árvore cadastrada com sucesso!');
    }

    /* ============================================================
     * LISTA AS PENDENTES
     * ============================================================ */
    public function pendingTrees()
    {
        // Sem species no with
        $pendingTrees = Tree::where('aprovado', false)->get();
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
            'trees' => Tree::with(['admin'])->latest()->get(),
        ]);
    }

    /* ============================================================
     * EDITAR
     * ============================================================ */
   /* ============================================================
     * EDITAR
     * ============================================================ */
    public function adminTreeEdit(Tree $tree)
    {
        // Mesma lógica para o edit
        $scientificNames = Tree::whereNotNull('scientific_name')
            ->where('scientific_name', '!=', '')
            ->distinct()
            ->orderBy('scientific_name')
            ->pluck('scientific_name');

        return view('admin.trees.edit', [
            'tree' => $tree,
            'bairros' => Bairro::orderBy('nome')->get(),
            'scientificNames' => $scientificNames, // Envia para a view
        ]);
    }

    /* ============================================================
     * ATUALIZAR
     * ============================================================ */
    public function adminTreeUpdate(Request $request, Tree $tree)
    {
        $validated = $request->validate([
            'scientific_name' => 'nullable|string|max:255',
            'vulgar_name' => 'nullable|string|max:255',
            // ... (mesmas validações dos outros campos)
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'health_status' => 'nullable|in:good,fair,poor',
            'planted_at' => 'nullable|date|before_or_equal:today',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
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
            'no_species_case' => 'nullable|string|max:255',
        ]);

        $updateData = collect($validated)->except(['species_name'])->toArray();

        $tree->update($updateData);

        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'update_tree',
            'description' => 'Árvore atualizada (ID ' . $tree->id . ')',
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
        // Removemos species
        $trees = Tree::all(); 

        return view('analista.map', compact('bairros', 'trees'));
    }
    
    public function analystTreeList()
    {
        $trees = Tree::all(); 
        return view('analista.trees.index', compact('trees'));
    }
}