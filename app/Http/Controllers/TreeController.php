<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Tree;
use App\Models\Species;
use App\Models\Activity;
use App\Models\AdminLog;
use App\Models\Bairro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    /**
     * Mostra a página inicial pública (mapa e estatísticas)
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

        $bairros = Bairro::orderBy('nome', 'asc')->get();

        return view('welcome', compact('stats', 'recentActivities', 'bairros'));
    }

    /**
     * Retorna dados das árvores em formato JSON (para o mapa)
     */
    public function getTreesData()
    {
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

    /**
     * Exibe os detalhes de uma árvore específica
     */
    public function show($id)
    {
        $tree = Tree::with(['species', 'user', 'activities.user'])->findOrFail($id);
        return view('trees.show', compact('tree'));
    }

    /**
     * Painel administrativo principal (Dashboard Admin)
     * Exibe estatísticas gerais e logs recentes
     */
        // 🌿 Painel principal do admin (dashboard com estatísticas e logs)
    public function adminDashboard()
    {
        $stats = [
            'total_trees' => Tree::count(),
            'total_activities' => Activity::count(),
            'total_species' => Species::count(),
        ];

        $adminLogs = AdminLog::latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'adminLogs'));
    }

    // 🌳 Página de cadastro de árvores (mapa)
    public function adminMap()
    {
        $species = Species::all();
        $trees = Tree::with('species')->get();

        return view('admin.trees.map', compact('species', 'trees'));
    }


    /**
     * Cadastra uma nova árvore (Admin)
     */
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

    // novos campos
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
    'no_species_case' => 'nullable|string|max:255',
]);

        // Cria ou busca espécie
        $species = Species::firstOrCreate(
            ['name' => $validated['species_name']],
            [
                'scientific_name' => null,
                'description' => 'Espécie adicionada via painel administrativo',
                'color_code' => '#' . substr(md5($validated['species_name']), 0, 6),
            ]
        );
         // Cria árvore
        $tree = Tree::create([
            'species_id' => $species->id,
            'user_id' => auth()->id(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'trunk_diameter' => $validated['trunk_diameter'] ?? null,
            'health_status' => $validated['health_status'],
            'planted_at' => $validated['planted_at'],
            'address' => $validated['address'] ?? $validated['name'],

            // novos campos
            'vulgar_name' => $validated['vulgar_name'] ?? null,
            'scientific_name' => $validated['scientific_name'] ?? null,
            'cap' => $validated['cap'] ?? null,
            'height' => $validated['height'] ?? null,
            'crown_height' => $validated['crown_height'] ?? null,
            'crown_diameter_longitudinal' => $validated['crown_diameter_longitudinal'] ?? null,
            'crown_diameter_perpendicular' => $validated['crown_diameter_perpendicular'] ?? null,
            'bifurcation_type' => $validated['bifurcation_type'] ?? null,
            'stem_balance' => $validated['stem_balance'] ?? null,
            'crown_balance' => $validated['crown_balance'] ?? null,
            'organisms' => $validated['organisms'] ?? null,
            'target' => $validated['target'] ?? null,
            'injuries' => $validated['injuries'] ?? null,
            'wiring_status' => $validated['wiring_status'] ?? null,
            'total_width' => $validated['total_width'] ?? null,
            'street_width' => $validated['street_width'] ?? null,
            'gutter_height' => $validated['gutter_height'] ?? null,
            'gutter_width' => $validated['gutter_width'] ?? null,
            'gutter_length' => $validated['gutter_length'] ?? null,
            'no_species_case' => $validated['no_species_case'] ?? null,
        ]);


        // Log de ação administrativa
        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_tree',
            'description' => 'O admin ' . Auth::user()->name . ' cadastrou a nova árvore: ' . $species->name . ' (ID: ' . $tree->id . ').'
        ]);

        
      // 🔹 Corrigido: redireciona para o painel correto
        return redirect()->route('admin.dashboard')->with('success', 'Árvore adicionada com sucesso!');
    }

    /**
     * Lista de árvores (Admin)
     */
    public function adminTreeList()
    {
        $trees = Tree::with('species')->latest()->get();
        return view('admin.trees.index', compact('trees'));
    }

    /**
     * Editar árvore (Admin)
     */
    public function adminTreeEdit(Tree $tree)
    {
        $species = Species::all();
        return view('admin.trees.edit', compact('tree', 'species'));
    }

    /**
     * Atualizar árvore (Admin)
     */
    public function adminTreeUpdate(Request $request, Tree $tree)
    {
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

    /**
     * Excluir árvore (Admin)
     */
    public function adminTreeDestroy(Tree $tree)
    {
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
