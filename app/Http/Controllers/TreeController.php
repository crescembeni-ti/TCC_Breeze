<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\AdminLog;
use App\Models\Bairro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Storage; 
use App\Exports\TreesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Contact;

class TreeController extends Controller
{
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
            'photo' => $tree->photo ? asset('storage/' . $tree->photo) : null,
        ]);
    }

    public function exportTrees(Request $request)
    {
        if (auth('admin')->check() || auth('analyst')->check()) {
            $user = auth('admin')->user() ?? auth('analyst')->user();
            $guard = auth('admin')->check() ? 'admin' : 'analyst';
            
            AdminLog::create([
                'admin_id' => $guard === 'admin' ? $user->id : null,
                'action' => 'export_trees',
                'description' => "Exportação massiva de árvores realizada por " . ($guard === 'admin' ? 'Admin' : 'Analista') . ": " . $user->name . " (ID: " . $user->id . ")"
            ]);
        }

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

        // Extrair opções únicas dos campos para popular os selects dinamicamente
        $fields = ['health_status', 'bifurcation_type', 'stem_balance', 'crown_balance', 'organisms', 'target', 'injuries', 'wiring_status'];
        $dynamicOptions = [];
        foreach ($fields as $field) {
            $queryOptions = Tree::whereNotNull($field)
                ->where($field, '!=', '')
                ->distinct()
                ->pluck($field)
                ->toArray();

            $baseOptions = [];
            if ($field === 'health_status') {
                $baseOptions = ['Boa', 'Regular', 'Ruim'];
            }

            $options = collect(array_merge($baseOptions, $queryOptions))
                ->map(function($opt) use ($field) {
                    $normalized = trim($opt);
                    $lower = strtolower($normalized);
                    
                    if ($field === 'injuries' && $lower === 'leves ou ausentes') return 'Leves ou Ausentes';
                    if ($field === 'crown_balance' && (str_contains($lower, 'medianamente') || str_contains($lower, 'mediamente'))) return 'Medianamente Desequilibrada';
                    if ($field === 'organisms') {
                        if ($lower === 'infestação média' || $lower === 'infestação media') return 'Infestação Média';
                        if ($lower === 'infestação avançada' || $lower === 'infestação avancada') return 'Infestação Avançada';
                    }
                    
                    if ($normalized === 'Boa' || $normalized === 'Regular' || $normalized === 'Ruim') return $normalized;
                    if ($normalized === 'Leves ou Ausentes') return $normalized;
                    
                    return mb_convert_case($normalized, MB_CASE_TITLE, "UTF-8");
                })
                ->unique()
                ->values()
                ->toArray();
                
            $dynamicOptions[$field] = $options;
        }

        return view('admin.trees.map', [
            'trees' => Tree::with(['bairro'])->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
            'scientificNames' => $scientificNames,
            'vulgarNames' => $vulgarNames,
            'speciesMap' => $speciesMap,
            'vulgarToScientific' => $vulgarToScientific,
            'dynamicOptions' => $dynamicOptions,
        ]);
    }

    public function storeTree(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', 
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
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('trees', 'public');
        }

        $tree = Tree::create($validated);

        AdminLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'create_tree',
            'description' => "Cadastrou a árvore ID: {$tree->id}"
        ]);

        return redirect()->route('admin.map')->with(['success' => 'Árvore cadastrada com sucesso!', 'new_tree_id' => $tree->id]);
    }

    public function adminTreeList(Request $request) 
    {
        $query = Tree::with('bairro');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('scientific_name', 'like', '%' . $request->search . '%')
                  ->orWhere('vulgar_name', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('bairro')) {
            $query->whereHas('bairro', function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->bairro . '%');
            });
        }

        $sort = $request->get('sort', 'id_desc');
        switch ($sort) {
            case 'id_asc': $query->orderBy('id', 'asc'); break;
            case 'id_desc': $query->orderBy('id', 'desc'); break;
            case 'name_asc': $query->orderBy('scientific_name', 'asc'); break;
            case 'name_desc': $query->orderBy('scientific_name', 'desc'); break;
            default: $query->orderBy('id', 'desc'); break;
        }

        $trees = $query->get();
        $bairros = Bairro::orderBy('nome')->get();
        return view('admin.trees.index', compact('trees', 'bairros')); 
    }

    public function adminTreeEdit(Tree $tree) 
    {
        $scientificNames = Tree::whereNotNull('scientific_name')->distinct()->pluck('scientific_name');
        $vulgarNames = Tree::whereNotNull('vulgar_name')->distinct()->pluck('vulgar_name');
        
        $speciesMap = Tree::select('scientific_name', 'vulgar_name')->distinct()->get()->mapWithKeys(fn($i) => [$i->scientific_name => $i->vulgar_name]);
        $vulgarToScientific = Tree::select('scientific_name', 'vulgar_name')->distinct()->get()->mapWithKeys(fn($i) => [$i->vulgar_name => $i->scientific_name]);

        // Extrair opções únicas dos campos para popular os selects dinamicamente (para edição)
        $fields = ['health_status', 'bifurcation_type', 'stem_balance', 'crown_balance', 'organisms', 'target', 'injuries', 'wiring_status'];
        $dynamicOptions = [];
        foreach ($fields as $field) {
            $queryOptions = Tree::whereNotNull($field)
                ->where($field, '!=', '')
                ->distinct()
                ->pluck($field)
                ->toArray();

            $baseOptions = [];
            if ($field === 'health_status') {
                $baseOptions = ['Boa', 'Regular', 'Ruim'];
            }

            $options = collect(array_merge($baseOptions, $queryOptions))
                ->map(function($opt) use ($field) {
                    $normalized = trim($opt);
                    $lower = strtolower($normalized);
                    
                    if ($field === 'injuries' && $lower === 'leves ou ausentes') return 'Leves ou Ausentes';
                    if ($field === 'crown_balance' && (str_contains($lower, 'medianamente') || str_contains($lower, 'mediamente'))) return 'Medianamente Desequilibrada';
                    if ($field === 'organisms') {
                        if ($lower === 'infestação média' || $lower === 'infestação media') return 'Infestação Média';
                        if ($lower === 'infestação avançada' || $lower === 'infestação avancada') return 'Infestação Avançada';
                    }
                    
                    if ($normalized === 'Boa' || $normalized === 'Regular' || $normalized === 'Ruim') return $normalized;
                    if ($normalized === 'Leves ou Ausentes') return $normalized;
                    
                    return mb_convert_case($normalized, MB_CASE_TITLE, "UTF-8");
                })
                ->unique()
                ->values()
                ->toArray();
                
            $dynamicOptions[$field] = $options;
        }

        return view('admin.trees.edit', [
            'tree' => $tree, 
            'bairros' => Bairro::orderBy('nome')->get(), 
            'scientificNames' => $scientificNames, 
            'vulgarNames' => $vulgarNames, 
            'speciesMap' => $speciesMap, 
            'vulgarToScientific' => $vulgarToScientific,
            'dynamicOptions' => $dynamicOptions,
        ]);
    }

    public function adminTreeUpdate(Request $request, Tree $tree) 
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90', 
            'longitude' => 'required|numeric|between:-180,180', 
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', 
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
        ]);

        if ($request->hasFile('photo')) {
            if ($tree->photo) Storage::disk('public')->delete($tree->photo);
            $validated['photo'] = $request->file('photo')->store('trees', 'public');
        }

        $tree->update($validated);

        AdminLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'update_tree',
            'description' => "Atualizou a árvore ID: {$tree->id}"
        ]);

        return redirect()->route('admin.trees')->with('success', 'Árvore atualizada com sucesso!');
    }

    public function adminTreeDestroy(Tree $tree) 
    {
        if ($tree->photo) Storage::disk('public')->delete($tree->photo);
        
        AdminLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => 'delete_tree',
            'description' => "Excluiu a árvore ID: {$tree->id}"
        ]);

        $tree->delete();
        return redirect()->route('admin.trees')->with('success', 'Árvore excluída com sucesso!');
    }
}
