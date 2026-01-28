<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\Activity;
use App\Models\AdminLog;
use App\Models\Bairro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Exports\TreesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Contact;

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

        // Filtros da Welcome
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
     * DADOS DO MAPA (API JSON)
     * ============================================================ */
    public function getTreesData(Request $request)
    {
        $query = Tree::with(['bairro', 'admin']) 
            ->where('aprovado', true)
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->where('latitude', '!=', 0)->where('longitude', '!=', 0);

        if ($request->filled('scientific_name')) {
            $query->where('scientific_name', $request->scientific_name);
        }
        if ($request->filled('bairro_id')) {
            $query->where('bairro_id', $request->bairro_id);
        }

        return $query->get()->map(fn ($tree) => [
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
     * EXPORTAR EXCEL
     * ============================================================ */
    public function exportTrees(Request $request)
    {
        $fileName = 'relatorio_arvores_' . date('d-m-Y_H-i') . '.xlsx';
        return Excel::download(new TreesExport($request), $fileName);
    }

    /* ============================================================
     * VISUALIZAÇÃO
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
            
            // ✅ ESTA É A LINHA QUE ESTÁ FALTANDO E CAUSA O ERRO
            'total_requests' => Contact::count(),
            
            'total_activities' => Activity::count(),
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'),
        ];

        $query = AdminLog::with('admin')->latest();

        // Filtros (Mantidos iguais)
        if ($request->filled('filter')) {
            $f = $request->filter;
            if ($f == 'cadastro') $query->where('action', 'like', '%create%');
            elseif ($f == 'atualizacao') $query->where('action', 'like', '%update%');
            elseif ($f == 'exclusao') $query->where('action', 'like', '%delete%');
            elseif ($f == 'aprovacao') $query->where('action', 'like', '%approve%');
        }

        if ($request->filled('period')) {
            $p = $request->period;
            if ($p == '7_days') $query->where('created_at', '>=', now()->subDays(7));
            elseif ($p == '30_days') $query->where('created_at', '>=', now()->subDays(30));
            elseif ($p == 'year') $query->where('created_at', '>=', now()->subYear());
        }

        $adminLogs = $query->paginate(10)->appends($request->all());

        return view('admin.dashboard', compact('stats', 'adminLogs'));
    }

    /* ============================================================
     * MAPA ADMIN (CADASTRO)
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
            ->where('vulgar_name', '!=', 'Não identificada')
            ->distinct()
            ->orderBy('vulgar_name')
            ->pluck('vulgar_name');

        // MAPA 1: Científico -> Popular
        $speciesMap = Tree::select('scientific_name', 'vulgar_name')
            ->whereNotNull('scientific_name')
            ->whereNotNull('vulgar_name')
            ->distinct()
            ->get()
            ->mapWithKeys(fn($i) => [$i->scientific_name => $i->vulgar_name]);

        // MAPA 2: Popular -> Científico (Reverso)
        $vulgarToScientific = Tree::select('scientific_name', 'vulgar_name')
            ->whereNotNull('scientific_name')
            ->whereNotNull('vulgar_name')
            ->distinct()
            ->get()
            ->mapWithKeys(fn($i) => [$i->vulgar_name => $i->scientific_name]);

        return view('admin.trees.map', [
            'trees' => Tree::with(['bairro'])->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
            'scientificNames' => $scientificNames,
            'vulgarNames' => $vulgarNames,
            'speciesMap' => $speciesMap,
            'vulgarToScientific' => $vulgarToScientific, // Envia para a View
        ]);
    }

    /* ============================================================
     * CADASTRAR ÁRVORE
     * ============================================================ */
    public function storeTree(Request $request)
    {
        $validated = $request->validate([
            // ATUALIZADO: Required
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            
            // ATUALIZADO: trunk_diameter removido
            'health_status' => 'nullable|string|max:255',
            'planted_at' => 'nullable|date|before_or_equal:today',
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
            'bifurcation_type' => 'nullable|string|max:500',
            'stem_balance' => 'nullable|string|max:500',
            'crown_balance' => 'nullable|string|max:500',
            'organisms' => 'nullable|string|max:255',
            'target' => 'nullable|string|max:500',
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
        if (empty($treeData['scientific_name'])) $treeData['scientific_name'] = 'Não identificada';
        if (empty($treeData['vulgar_name'])) $treeData['vulgar_name'] = 'Não identificada';

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

        if (auth()->guard('admin')->check()) {
            $nomeLog = $tree->vulgar_name ?? $tree->no_species_case ?? $tree->scientific_name;
            AdminLog::create([
                'admin_id' => auth()->guard('admin')->id(), 
                'action' => 'create_tree', 
                'description' => 'Árvore criada (ID ' . $tree->id . ') - Nome: ' . $nomeLog
            ]);
        }

        $msg = $treeData['aprovado'] ? 'Árvore cadastrada com sucesso!' : 'Árvore enviada para aprovação!';
        
        // Redireciona para a mesma página de cadastro
        $route = auth()->guard('admin')->check() ? 'admin.map' : 'analyst.map';

        return redirect()
            ->route($route)
            ->with('success', $msg)
            ->with('new_tree_id', $tree->id);
    }

    public function pendingTrees() 
    { 
        $pendingTrees = Tree::where('aprovado', false)->get(); 
        return view('admin.trees.pending', compact('pendingTrees')); 
    }

    public function approveTree($id) 
    { 
        $tree = Tree::findOrFail($id); 
        $tree->update(['aprovado' => true]); 
        if (auth()->guard('admin')->check()) { 
            AdminLog::create([
                'admin_id' => auth()->guard('admin')->id(), 
                'action' => 'approve_tree', 
                'description' => 'Aprovou a árvore ID ' . $tree->id
            ]); 
        } 
        return back()->with('success', 'Árvore aprovada e publicada no mapa!'); 
    }

    public function adminTreeList() 
    { 
        return view('admin.trees.index', ['trees' => Tree::with(['admin'])->latest()->get()]); 
    }

    // ATUALIZADO: Edit também recebe os mapas
    public function adminTreeEdit(Tree $tree) 
    {
        $scientificNames = Tree::whereNotNull('scientific_name')->distinct()->orderBy('scientific_name')->pluck('scientific_name');
        $vulgarNames = Tree::whereNotNull('vulgar_name')->distinct()->orderBy('vulgar_name')->pluck('vulgar_name');
        
        $speciesMap = Tree::select('scientific_name', 'vulgar_name')->distinct()->get()->mapWithKeys(fn($i) => [$i->scientific_name => $i->vulgar_name]);
        $vulgarToScientific = Tree::select('scientific_name', 'vulgar_name')->distinct()->get()->mapWithKeys(fn($i) => [$i->vulgar_name => $i->scientific_name]);
        
        return view('admin.trees.edit', [
            'tree' => $tree, 
            'bairros' => Bairro::orderBy('nome')->get(), 
            'scientificNames' => $scientificNames, 
            'vulgarNames' => $vulgarNames, 
            'speciesMap' => $speciesMap, 
            'vulgarToScientific' => $vulgarToScientific
        ]);
    }

    public function adminTreeUpdate(Request $request, Tree $tree) 
    {
        $validated = $request->validate([
            'scientific_name' => 'nullable|string|max:255', 
            'vulgar_name' => 'nullable|string|max:255', 
            // ATUALIZADO
            'latitude' => 'required|numeric|between:-90,90', 
            'longitude' => 'required|numeric|between:-180,180', 
            // DIÂMETRO REMOVIDO
            'health_status' => 'nullable|string|max:255', 
            'planted_at' => 'nullable|date|before_or_equal:today', 
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
            'target' => 'nullable|string|max:500', 
            'injuries' => 'nullable|string|max:255', 
            'wiring_status' => 'nullable|string|max:255', 
            'total_width' => 'nullable|numeric|min:0', 
            'street_width' => 'nullable|numeric|min:0', 
            'gutter_height' => 'nullable|numeric|min:0', 
            'gutter_width' => 'nullable|numeric|min:0', 
            'gutter_length' => 'nullable|numeric|min:0', 
            'description' => 'nullable|string|max:1000'
        ]);

        $updateData = $validated;

        // TRAVA DE SEGURANÇA BACKEND: Se for Analista, filtra apenas os campos permitidos
        if (auth('analyst')->check()) {
            $updateData = $request->only([
                'stem_balance', 
                'wiring_status', 
                'target'
            ]);
            
            // Mantém os dados originais que não podem ser alterados
            // Isso evita que um hacker envie campos extras via Postman/Inspecionar
        } else {
            if (empty($updateData['scientific_name'])) $updateData['scientific_name'] = 'Não identificada';
            if (empty($updateData['vulgar_name'])) $updateData['vulgar_name'] = 'Não identificada';
        }
        
        $tree->update($updateData);
        
        if (auth('admin')->check()) { 
            $nomeLog = $tree->vulgar_name ?? $tree->no_species_case ?? 'Atualizada'; 
            AdminLog::create([
                'admin_id' => auth('admin')->id(), 
                'action' => 'update_tree', 
                'description' => 'Árvore atualizada (ID ' . $tree->id . ') - Nome: ' . $nomeLog
            ]); 
        }
        $route = auth('admin')->check() ? 'admin.trees.edit' : 'admin.trees.edit'; // A rota é a mesma, mas o middleware agora permite ambos
        return redirect()->route('admin.trees.edit', $tree->id)->with('success', 'Árvore atualizada com sucesso!');
    }

    public function adminTreeDestroy(Tree $tree) 
    { 
        $id = $tree->id; 
        $tree->delete(); 
        if (auth('admin')->check()) { 
            AdminLog::create([
                'admin_id' => auth('admin')->id(), 
                'action' => 'delete_tree', 
                'description' => "Árvore deletada (ID $id)"
            ]); 
        } 
        return redirect()->route('admin.trees.index')->with('success', 'Árvore excluída!'); 
    }
    
    public function analystMap() 
    { 
        $bairros = Bairro::orderBy('nome')->get(); 
        $trees = Tree::all(); 
        $scientificNames = Tree::whereNotNull('scientific_name')->distinct()->orderBy('scientific_name')->pluck('scientific_name');
        $vulgarNames = Tree::whereNotNull('vulgar_name')->distinct()->orderBy('vulgar_name')->pluck('vulgar_name');
        
        $speciesMap = Tree::select('scientific_name', 'vulgar_name')->distinct()->get()->mapWithKeys(fn($i) => [$i->scientific_name => $i->vulgar_name]);
        $vulgarToScientific = Tree::select('scientific_name', 'vulgar_name')->distinct()->get()->mapWithKeys(fn($i) => [$i->vulgar_name => $i->scientific_name]);
        
        return view('analista.map', compact('bairros', 'trees', 'scientificNames', 'vulgarNames', 'speciesMap', 'vulgarToScientific')); 
    }
    
    public function analystTreeList() 
    { 
        $trees = Tree::all(); 
        return view('analista.trees.index', compact('trees')); 
    }
}