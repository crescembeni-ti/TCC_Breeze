<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\Activity;
use App\Models\AdminLog;
use App\Models\Bairro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
// Importações necessárias para o Excel
use App\Exports\TreesExport;
use Maatwebsite\Excel\Facades\Excel;

class TreeController extends Controller
{
    /* ============================================================
     * PÁGINA PÚBLICA (HOME)
     * ============================================================ */
    public function index()
    {
        $stats = [
            'total_trees' => Tree::where('aprovado', true)->count(),
            'total_activities' => Activity::count(),
            'total_species' => Tree::where('aprovado', true)->distinct('scientific_name')->count('scientific_name'), 
        ];

        $recentActivities = Activity::with(['tree', 'user'])
            ->orderBy('activity_date', 'desc')
            ->take(5)
            ->get();

        $bairros = Bairro::orderBy('nome')->get();

        // --- ADICIONADO: Listas para os filtros da Welcome (Apenas Aprovadas) ---
        $scientificNames = Tree::where('aprovado', true)
            ->whereNotNull('scientific_name')
            ->where('scientific_name', '!=', '')
            ->distinct()
            ->orderBy('scientific_name')
            ->pluck('scientific_name');

        $vulgarNames = Tree::where('aprovado', true)
            ->whereNotNull('vulgar_name')
            ->where('vulgar_name', '!=', '')
            ->where('vulgar_name', '!=', 'Não identificada')
            ->distinct()
            ->orderBy('vulgar_name')
            ->pluck('vulgar_name');

        return view('welcome', compact('stats', 'recentActivities', 'bairros', 'scientificNames', 'vulgarNames'));
    }

    /* ============================================================
     * DADOS DO MAPA (API JSON) - COM FILTROS
     * ============================================================ */
    public function getTreesData(Request $request)
    {
        $query = Tree::with(['bairro', 'admin']) 
            ->where('aprovado', true)
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->where('latitude', '!=', 0)->where('longitude', '!=', 0);

        // --- APLICAÇÃO DOS FILTROS ---
        if ($request->filled('scientific_name')) {
            $query->where('scientific_name', $request->scientific_name);
        }

        if ($request->filled('bairro_id')) {
            $query->where('bairro_id', $request->bairro_id);
        }

        return $query->get()
            ->map(fn ($tree) => [
                'id' => $tree->id,
                'latitude' => (float) $tree->latitude,
                'longitude' => (float) $tree->longitude,
                'species_name' => $tree->scientific_name ?? $tree->vulgar_name ?? 'Árvore Não Identificada',
                'vulgar_name'  => $tree->vulgar_name ?? 'Não Identificada',
                'color_code' => '#358054', 
                'address' => $tree->address,
                'bairro_id' => $tree->bairro_id,
                'bairro_nome' => $tree->bairro->nome ?? null,
                'trunk_diameter' => $tree->trunk_diameter,
                'registered_by' => $tree->admin ? $tree->admin->name : 'Sistema',

                // Campos extras para o popup do Admin (usados no JS da home)
                'health_status' => $tree->health_status,
                'bifurcation_type' => $tree->bifurcation_type,
                'stem_balance' => $tree->stem_balance,
                'crown_balance' => $tree->crown_balance,
                'organisms' => $tree->organisms,
                'target' => $tree->target,
                'injuries' => $tree->injuries,
                'wiring_status' => $tree->wiring_status,
            ]);
    }

    /* ============================================================
     * EXPORTAR PARA EXCEL (.xlsx)
     * ============================================================ */
    public function exportTrees(Request $request)
    {
        $fileName = 'relatorio_arvores_' . date('d-m-Y_H-i') . '.xlsx';
        return Excel::download(new TreesExport($request), $fileName);
    }

    /* ============================================================
     * VISUALIZAÇÃO (DETALHES)
     * ============================================================ */
    public function show($id)
    {
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
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'),
        ];

        $query = AdminLog::with('admin')->latest();

        // 1. Filtro de Tipo de Ação
        if ($request->filled('filter')) {
            $filter = $request->filter;
            if ($filter == 'cadastro') {
                $query->where('action', 'like', '%create%');
            } elseif ($filter == 'atualizacao') {
                $query->where('action', 'like', '%update%');
            } elseif ($filter == 'exclusao') {
                $query->where('action', 'like', '%delete%');
            } elseif ($filter == 'aprovacao') {
                $query->where('action', 'like', '%approve%');
            }
        }

        // 2. Filtro de Período (Data)
        if ($request->filled('period')) {
            $period = $request->period;
            if ($period == '7_days') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($period == '30_days') {
                $query->where('created_at', '>=', now()->subDays(30));
            } elseif ($period == 'year') {
                $query->where('created_at', '>=', now()->subYear());
            }
        }

        $adminLogs = $query->paginate(10)->appends($request->all());

        return view('admin.dashboard', compact('stats', 'adminLogs'));
    }

    /* ============================================================
     * MAPA ADMIN (CADASTRO E VISUALIZAÇÃO)
     * ============================================================ */
    public function adminMap()
    {
        $scientificNames = Tree::whereNotNull('scientific_name')
            ->where('scientific_name', '!=', '')
            ->distinct()
            ->orderBy('scientific_name')
            ->pluck('scientific_name');

        $vulgarNames = Tree::whereNotNull('vulgar_name')
            ->where('vulgar_name', '!=', '')
            ->where('vulgar_name', '!=', 'Não identificada') // Opcional: remove o padrão
            ->distinct()
            ->orderBy('vulgar_name')
            ->pluck('vulgar_name');

        return view('admin.trees.map', [
            'trees' => Tree::with(['bairro'])->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
            'scientificNames' => $scientificNames,
            'vulgarNames' => $vulgarNames, // <--- NÃO ESQUEÇA DE PASSAR AQUI
        ]);
    }

    /* ============================================================
     * CADASTRAR ÁRVORE
     * ============================================================ */
    public function storeTree(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'health_status' => 'nullable|string|max:255',
            'planted_at' => 'nullable|date|before_or_equal:today',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
            'vulgar_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'no_species_case' => 'nullable|string|max:255',
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

        $treeData = $validated;

        if (empty($treeData['scientific_name'])) {
            $treeData['scientific_name'] = 'Não identificada';
        }
        if (empty($treeData['vulgar_name'])) {
            $treeData['vulgar_name'] = 'Não identificada';
        }

        // Lógica de aprovação automática
        if (auth()->guard('analyst')->check()) {
            $treeData['admin_id'] = null;
            $treeData['analyst_id'] = auth()->guard('analyst')->id();
            $treeData['aprovado'] = 0; 
        } elseif (auth()->guard('admin')->check()) {
            $treeData['admin_id'] = auth()->guard('admin')->id();
            $treeData['analyst_id'] = null;
            $treeData['aprovado'] = 1;
        } else {
            $treeData['aprovado'] = 0;
        }

        $tree = Tree::create($treeData);

        // Registro de Log
        if (auth()->guard('admin')->check()) {
            AdminLog::create([
                'admin_id' => auth()->guard('admin')->id(),
                'action' => 'create_tree',
                'description' => 'Árvore criada (ID ' . $tree->id . ') - Nome: ' . $nomeLog,
            ]);
        }



        $msg = $treeData['aprovado'] ? 'Árvore cadastrada com sucesso!' : 'Árvore enviada para aprovação!';
        
        // CORREÇÃO AQUI: Mudamos 'analyst.map.create' para 'analyst.map'
        $route = auth()->guard('admin')->check() ? 'admin.map' : 'analyst.map';

        return redirect()
            ->route($route) 
            ->with('success', $msg)
            ->with('new_tree_id', $tree->id);
    }

    /* ============================================================
     * LISTA DE PENDENTES
     * ============================================================ */
    public function pendingTrees()
    {
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
            AdminLog::create([
                'admin_id' => auth()->guard('admin')->id(),
                'action' => 'approve_tree',
                'description' => 'Aprovou a árvore ID ' . $tree->id,
            ]);
        }

        return back()->with('success', 'Árvore aprovada e publicada no mapa!');
    }

    /* ============================================================
     * LISTA ADMIN
     * ============================================================ */
    public function adminTreeList()
    {
        return view('admin.trees.index', [
            'trees' => Tree::with(['admin'])->latest()->get(),
        ]);
    }

    /* ============================================================
     * EDITAR ADMIN
     * ============================================================ */
    public function adminTreeEdit(Tree $tree)
    {
        $scientificNames = Tree::whereNotNull('scientific_name')
            ->where('scientific_name', '!=', '')
            ->where('scientific_name', '!=', 'Não identificada')
            ->distinct()
            ->orderBy('scientific_name')
            ->pluck('scientific_name');

        $vulgarNames = Tree::whereNotNull('vulgar_name')
            ->where('vulgar_name', '!=', '')
            ->where('vulgar_name', '!=', 'Não identificada')
            ->distinct()
            ->orderBy('vulgar_name')
            ->pluck('vulgar_name');

        return view('admin.trees.edit', [
            'tree' => $tree,
            'bairros' => Bairro::orderBy('nome')->get(),
            'scientificNames' => $scientificNames,
            'vulgarNames' => $vulgarNames,
        ]);
    }

    /* ============================================================
     * ATUALIZAR ADMIN
     * ============================================================ */
    public function adminTreeUpdate(Request $request, Tree $tree)
    {
        $validated = $request->validate([
            'scientific_name' => 'nullable|string|max:255',
            'vulgar_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'health_status' => 'nullable|string|max:255',
            'planted_at' => 'nullable|date|before_or_equal:today',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
            'no_species_case' => 'nullable|string|max:255',
            
            'cap' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'crown_height' => 'nullable|numeric|min:0',
            'crown_diameter_longitudinal' => 'nullable|numeric|min:0',
            'crown_diameter_perpendicular' => 'nullable|numeric|min:0',
            'bifurcation_type' => 'nullable|string|max:255',
            'stem_balance' => 'nullable|string|max:500',
            'crown_balance' => 'nullable|string|max:255',
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

        $updateData = $validated;

        if (empty($updateData['scientific_name'])) {
            $updateData['scientific_name'] = 'Não identificada';
        }
        if (empty($updateData['vulgar_name'])) {
            $updateData['vulgar_name'] = 'Não identificada';
        }

        $tree->update($updateData);

        $nomeLog = $tree->vulgar_name ?? $tree->no_species_case ?? 'Atualizada';

        if (auth('admin')->check()) {
            $nomeLog = $tree->vulgar_name ?? $tree->no_species_case ?? 'Atualizada';
            AdminLog::create([
                'admin_id' => auth('admin')->id(),
                'action' => 'update_tree',
                'description' => 'Árvore atualizada (ID ' . $tree->id . ') - Nome: ' . $nomeLog,
            ]);
        }

        return redirect()
            ->route('admin.trees.edit', $tree->id)
            ->with('success', 'Árvore atualizada com sucesso!')
            ->with('updated_tree_id', $tree->id);
    }

    /* ============================================================
     * EXCLUIR
     * ============================================================ */
    public function adminTreeDestroy(Tree $tree)
    {
        $id = $tree->id;
        $tree->delete();

        if (auth('admin')->check()) {
            AdminLog::create([
                'admin_id' => auth('admin')->id(),
                'action' => 'delete_tree',
                'description' => "Árvore deletada (ID $id)",
            ]);
        }

        return redirect()->route('admin.trees.index')->with('success', 'Árvore excluída!');
    }

    // ==========================================================
    // ÁREA DO ANALISTA
    // ==========================================================

    public function analystMap()
    {
        $bairros = Bairro::orderBy('nome')->get();
        
        // 1. Busca todas as árvores para mostrar no mapa
        $trees = Tree::all(); 

        $scientificNames = Tree::whereNotNull('scientific_name')
            ->where('scientific_name', '!=', '')
            ->distinct()
            ->orderBy('scientific_name')
            ->pluck('scientific_name')
            ->toArray();

        // Lista de apoio para autocomplete
        $commonSpecies = [
            'Caesalpinia echinata (Pau-Brasil)',
            'Handroanthus albus (Ipê-Amarelo)',
            'Handroanthus heptaphyllus (Ipê-Roxo)',
            'Tabebuia roseoalba (Ipê-Branco)',
            'Delonix regia (Flamboyant)',
            'Spathodea campanulata (Bisnagueira)',
            'Terminalia catappa (Amendoeira)',
            'Ficus benjamina (Ficus)',
            'Mangifera indica (Mangueira)',
            'Tibouchina granulosa (Quaresmeira)',
            'Schinus terebinthifolia (Aroeira)',
            'Jacaranda mimosifolia (Jacarandá-Mimoso)',
            'Licania tomentosa (Oiti)'
        ];

        // 4. Junta tudo, remove duplicados e ordena
        $allSpecies = array_unique(array_merge($registeredSpecies, $commonSpecies));
        sort($allSpecies);
        $species = collect($allSpecies)->map(fn($name) => ['name' => $name]);

        return view('analista.map', compact('bairros', 'trees', 'scientificNames', 'vulgarNames'));
    }
    
    public function analystTreeList()
    {
        $trees = Tree::all(); 
        return view('analista.trees.index', compact('trees'));
    }
}