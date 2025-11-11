<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Tree;
use App\Models\Species;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;
use App\Models\Bairro; // Importação já estava correta

class TreeController extends Controller
{
    
    // 1. MÉTODO getBairros() REMOVIDO
    //    Não precisamos mais dele, pois vamos carregar os
    //    bairros direto no método index() abaixo.

    /**
     * Mostra a página principal (welcome) com estatísticas,
     * atividades recentes e a lista de bairros para o filtro.
     */
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

        // 2. BUSCA A LISTA DE BAIRROS DO BANCO
        //    Usar o Model 'Bairro' é mais limpo que 'DB::table'
        $bairros = Bairro::orderBy('nome', 'asc')->get();

        // 3. ENVIA A NOVA VARIÁVEL '$bairros' PARA A VIEW
        return view('welcome', compact('stats', 'recentActivities', 'bairros'));
    }

    public function getTreesData()
    {
        // ... (Este método está correto, sem alterações)
        $trees = Tree::with('species')->get()->map(function ($tree) {
            return [
                'id' => $tree->id,
                'latitude' => (float) $tree->latitude,
                'longitude' => (float) $tree->longitude,
                'species_name' => $tree->species->name,
                'color_code' => $tree->species->color_code,
                'trunk_diameter' => (float) $tree->trunk_diameter,
                'address' => $tree->address,
                'health_status' => $tree->health_status,
            ];
        });

        return response()->json($trees);
    }

    public function show($id)
    {
        // ... (Sem alterações)
        $tree = Tree::with(['species', 'user', 'activities.user'])->findOrFail($id);
        
        return view('trees.show', compact('tree'));
    }

    public function adminMap()
    {
        // ... (Sem alterações)
        $species = Species::all();
        $trees = Tree::with('species')->get();
        
        return view('dashboard.map', compact('species', 'trees'));
    }

    public function storeTree(Request $request)
    {
        // ... (Sem alterações, seu log de admin está correto)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'species_name' => 'required|string|max:255',
            'health_status' => 'required|in:good,fair,poor',
            'planted_at' => 'required|date',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
        ]);

        $species = Species::firstOrCreate(
            ['name' => $validated['species_name']],
            [
                'scientific_name' => null,
                'description' => 'Espécie adicionada via painel administrativo',
                'color_code' => '#' . substr(md5($validated['species_name']), 0, 6),
            ]
        );

        $tree = Tree::create([
            'species_id' => $species->id,
            'user_id' => auth()->id(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'trunk_diameter' => $validated['trunk_diameter'] ?? null,
            'health_status' => $validated['health_status'],
            'planted_at' => $validated['planted_at'],
            'address' => $validated['address'] ?? $validated['name'],
        ]);

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_tree',
            'description' => 'O admin ' . Auth::user()->name . ' cadastrou a nova árvore: ' . $species->name . ' (ID: ' . $tree->id . ').'
        ]);

        return redirect()->route('admin.map')->with('success', 'Árvore adicionada com sucesso!');
    }

    public function adminTreeList()
    {
        // ... (Sem alterações)
        $trees = Tree::with('species')->latest()->get(); 
        return view('admin.trees.index', compact('trees'));
    }

    public function adminTreeEdit(Tree $tree)
    {
        // ... (Sem alterações)
        $species = Species::all(); 
        return view('admin.trees.edit', compact('tree', 'species'));
    }

    public function adminTreeUpdate(Request $request, Tree $tree)
    {
        // ... (Sem alterações, seu log de admin está correto)
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'species_id' => 'required|integer|exists:species,id', 
            'health_status' => 'required|in:good,fair,poor',
            'planted_at' => 'required|date',
            'trunk_diameter' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
        ]);

        $tree->update($validated);

        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_tree',
            'description' => 'O admin ' . Auth::user()->name . ' atualizou a árvore ' . $tree->species->name . ' (ID: ' . $tree->id . ').'
        ]);

        return redirect()->route('admin.trees.index')->with('success', 'Árvore atualizada com sucesso!');
    }

    public function adminTreeDestroy(Tree $tree)
    {
        // ... (Sem alterações, seu log de admin está correto)
        $speciesName = $tree->species->name;
        $treeId = $tree->id;
        $tree->delete();
        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete_tree',
            'description' => 'O admin ' . Auth::user()->name . ' deletou a árvore ' . $speciesName . ' (ID: ' . $treeId . ').'
        ]);

        return redirect()->route('admin.trees.index')->with('success', 'Árvore deletada com sucesso!');
    }
}