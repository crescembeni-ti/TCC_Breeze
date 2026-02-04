<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\AdminLog;
use App\Models\Bairro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage; // Importante para deletar fotos antigas se necessário
use App\Exports\TreesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Contact;

class TreeController extends Controller
{
    // ... (métodos index, getTreesData, exportTrees, show, adminDashboard, adminMap mantidos iguais) ...

    public function index()
    {
        $stats = [
            'total_trees' => Tree::where('aprovado', true)->count(),
            'total_species' => Tree::where('aprovado', true)->distinct('scientific_name')->count('scientific_name'), 
        ];

        $bairros = Bairro::orderBy('nome')->get();

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

        return view('welcome', compact('stats', 'bairros', 'scientificNames', 'vulgarNames'));
    }

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
            'shading_area' => $tree->shading_area,
            'crown_diameter_longitudinal' => $tree->crown_diameter_longitudinal,
            'crown_diameter_perpendicular' => $tree->crown_diameter_perpendicular,
            // Adicionado caminho da foto para uso futuro no mapa se necessário
            'photo' => $tree->photo ? asset('storage/' . $tree->photo) : null,
        ]);
    }

    public function exportTrees(Request $request)
    {
        $fileName = 'relatorio_arvores_' . date('d-m-Y_H-i') . '.xlsx';
        return Excel::download(new TreesExport($request), $fileName);
    }

    public function show($id)
    {
        $tree = Tree::with(['admin'])->findOrFail($id);
        return view('trees.show', compact('tree'));
    }

    public function adminDashboard(Request $request)
    {
        $stats = [
            'total_trees' => Tree::count(),
            'total_requests' => Contact::count(),
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'),
        ];

        $query = AdminLog::with('admin')->latest();

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

        $speciesMap = Tree::select('scientific_name', 'vulgar_name')
            ->whereNotNull('scientific_name')
            ->whereNotNull('vulgar_name')
            ->distinct()
            ->get()
            ->mapWithKeys(fn($i) => [$i->scientific_name => $i->vulgar_name]);

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
            'vulgarToScientific' => $vulgarToScientific,
        ]);
    }

    /* ============================================================
     * CADASTRAR ÁRVORE (MODIFICADO PARA FOTO)
     * ============================================================ */
    public function storeTree(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // Validação da foto (Máx 10MB)
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
            'cap2' => 'nullable|numeric|min:0', 'cap3' => 'nullable|numeric|min:0', 'cap4' => 'nullable|numeric|min:0', 'cap5' => 'nullable|numeric|min:0',
            'cap6' => 'nullable|numeric|min:0', 'cap7' => 'nullable|numeric|min:0', 'cap8' => 'nullable|numeric|min:0', 'cap9' => 'nullable|numeric|min:0', 'cap10' => 'nullable|numeric|min:0',
            'cap11' => 'nullable|numeric|min:0', 'cap12' => 'nullable|numeric|min:0', 'cap13' => 'nullable|numeric|min:0', 'cap14' => 'nullable|numeric|min:0', 'cap15' => 'nullable|numeric|min:0',
            'cap16' => 'nullable|numeric|min:0', 'cap17' => 'nullable|numeric|min:0', 'cap18' => 'nullable|numeric|min:0', 'cap19' => 'nullable|numeric|min:0', 'cap20' => 'nullable|numeric|min:0',
        ]);

        $treeData = $validated;

        // UPLOAD DA FOTO
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // Salva na pasta 'storage/app/public/trees'
            $path = $request->file('photo')->store('trees', 'public');
            $treeData['photo'] = $path;
        }

        // Calcular DAPs
        if (!empty($treeData["cap"])) {
            $treeData["dap1"] = round($treeData["cap"] / pi(), 2);
        }
        for ($i = 2; $i <= 20; $i++) {
            if (!empty($treeData["cap$i"])) {
                $treeData["dap$i"] = round($treeData["cap$i"] / pi(), 2);
            }
        }
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
        $route = auth()->guard('admin')->check() ? 'admin.map' : 'analyst.map';

        return redirect()
            ->route($route)
            ->with('success', $msg)
            ->with('new_tree_id', $tree->id);
    }

    public function pendingTrees() 
    { 
        $pendingTrees = Tree::where('aprovado', 0)->with('analyst')->latest()->get(); 
        return view('admin.trees.pending', compact('pendingTrees')); 
    }

    public function approveTree($id) 
    { 
        $tree = Tree::findOrFail($id); 
        $tree->update(['aprovado' => 1]); 
        
        AdminLog::create([
            'admin_id' => auth('admin')->id(), 
            'action' => 'approve_tree', 
            'description' => "Árvore aprovada (ID $id)"
        ]); 
        
        return redirect()->back()->with('success', 'Árvore aprovada com sucesso!'); 
    }

    public function adminTreeList(Request $request) 
    { 
        $query = Tree::with('bairro');

        // Filtros
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('bairro')) {
            $query->whereHas('bairro', function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->bairro . '%');
            });
        }

        // Ordenação
        $sort = $request->get('sort', 'id_desc');
        switch ($sort) {
            case 'id_asc': $query->orderBy('id', 'asc'); break;
            case 'id_desc': $query->orderBy('id', 'desc'); break;
            case 'name_asc': $query->orderBy('scientific_name', 'asc'); break;
            case 'name_desc': $query->orderBy('scientific_name', 'desc'); break;
            default: $query->orderBy('id', 'desc'); break;
        }

        $trees = $query->get();
        return view('admin.trees.index', compact('trees')); 
    }

    public function adminTreeEdit(Tree $tree) 
    {
        $scientificNames = Tree::whereNotNull('scientific_name')->distinct()->pluck('scientific_name');
        $vulgarNames = Tree::whereNotNull('vulgar_name')->distinct()->pluck('vulgar_name');
        
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

    /* ============================================================
     * ATUALIZAR ÁRVORE (MODIFICADO PARA FOTO)
     * ============================================================ */
    public function adminTreeUpdate(Request $request, Tree $tree) 
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90', 
            'longitude' => 'required|numeric|between:-180,180', 
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // Validação da foto
            'scientific_name' => 'nullable|string|max:255', 
            'vulgar_name' => 'nullable|string|max:255', 
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
            'description' => 'nullable|string|max:1000',
            'cap2' => 'nullable|numeric|min:0', 'cap3' => 'nullable|numeric|min:0', 'cap4' => 'nullable|numeric|min:0', 'cap5' => 'nullable|numeric|min:0',
            'cap6' => 'nullable|numeric|min:0', 'cap7' => 'nullable|numeric|min:0', 'cap8' => 'nullable|numeric|min:0', 'cap9' => 'nullable|numeric|min:0', 'cap10' => 'nullable|numeric|min:0',
            'cap11' => 'nullable|numeric|min:0', 'cap12' => 'nullable|numeric|min:0', 'cap13' => 'nullable|numeric|min:0', 'cap14' => 'nullable|numeric|min:0', 'cap15' => 'nullable|numeric|min:0',
            'cap16' => 'nullable|numeric|min:0', 'cap17' => 'nullable|numeric|min:0', 'cap18' => 'nullable|numeric|min:0', 'cap19' => 'nullable|numeric|min:0', 'cap20' => 'nullable|numeric|min:0',
        ]);

        $updateData = $validated;

        // UPLOAD DA FOTO NO EDIT
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // (Opcional) Deletar a foto antiga para não acumular lixo
            // if ($tree->photo) { Storage::disk('public')->delete($tree->photo); }
            
            $path = $request->file('photo')->store('trees', 'public');
            $updateData['photo'] = $path;
        }

        // Calcular DAPs
        if (isset($updateData["cap"])) {
            if (!empty($updateData["cap"])) {
                $updateData["dap1"] = round($updateData["cap"] / pi(), 2);
            } else {
                $updateData["dap1"] = null;
            }
        }
        for ($i = 2; $i <= 20; $i++) {
            if (isset($updateData["cap$i"])) {
                if (!empty($updateData["cap$i"])) {
                    $updateData["dap$i"] = round($updateData["cap$i"] / pi(), 2);
                } else {
                    $updateData["cap$i"] = null;
                    $updateData["dap$i"] = null;
                }
            }
        }

        // TRAVA DE SEGURANÇA BACKEND: Se for Analista, filtra apenas os campos permitidos
        if (auth('analyst')->check()) {
            // ADICIONADO 'photo' À LISTA DE PERMISSÕES DO ANALISTA
            $updateData = $request->only([
                'stem_balance', 
                'wiring_status', 
                'target',
                'photo' // Analista pode editar a foto
            ]);
            
            // Re-aplicar o upload da foto caso o ->only() tenha removido se não foi enviado no request como campo simples
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                 $path = $request->file('photo')->store('trees', 'public');
                 $updateData['photo'] = $path;
            }
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
    
    public function analystTreeList(Request $request) 
    { 
        // Como o arquivo analista.trees.index não existe fisicamente, mas a rota aponta para ele,
        // vou redirecionar para a view admin.trees.index que é a que realmente existe.
        $query = Tree::with('bairro');

        // Filtros
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('bairro')) {
            $query->whereHas('bairro', function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->bairro . '%');
            });
        }

        // Ordenação
        $sort = $request->get('sort', 'id_desc');
        switch ($sort) {
            case 'id_asc': $query->orderBy('id', 'asc'); break;
            case 'id_desc': $query->orderBy('id', 'desc'); break;
            case 'name_asc': $query->orderBy('scientific_name', 'asc'); break;
            case 'name_desc': $query->orderBy('scientific_name', 'desc'); break;
            default: $query->orderBy('id', 'desc'); break;
        }

        $trees = $query->get();
        return view('admin.trees.index', compact('trees')); 
    }
}