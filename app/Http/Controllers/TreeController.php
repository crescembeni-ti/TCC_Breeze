<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\Species;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Models\AdminLog; // <-- 1. IMPORTAR O MODEL DE LOG
use Illuminate\Support\Facades\Auth; // <-- 2. IMPORTAR O AUTH PARA PEGAR O ADMIN LOGADO

class TreeController extends Controller
{
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

        return view('welcome', compact('stats', 'recentActivities'));
    }

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

    public function show($id)
    {
        $tree = Tree::with(['species', 'user', 'activities.user'])->findOrFail($id);
        
        return view('trees.show', compact('tree'));
    }

    public function adminMap()
    {
        $species = Species::all();
        $trees = Tree::with('species')->get();
        
        return view('dashboard.map', compact('species', 'trees'));
    }

    public function storeTree(Request $request)
    {
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

        // Buscar ou criar a espécie
        $species = Species::firstOrCreate(
            ['name' => $validated['species_name']],
            [
                'scientific_name' => null,
                'description' => 'Espécie adicionada via painel administrativo',
                'color_code' => '#' . substr(md5($validated['species_name']), 0, 6),
            ]
        );

        // Criar a árvore
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

        // --- 3. ADICIONADO LOG DE CRIAÇÃO ---
        AdminLog::create([
            'user_id' => Auth::id(), // Pega o ID do admin logado
            'action' => 'create_tree',
            'description' => 'O admin ' . Auth::user()->name . ' cadastrou a nova árvore: ' . $species->name . ' (ID: ' . $tree->id . ').'
        ]);
        // --- FIM DO LOG ---

        return redirect()->route('admin.map')->with('success', 'Árvore adicionada com sucesso!');
    }

    /**
     * [NOVO MÉTODO]
     * Mostra uma lista de todas as árvores para o admin.
     */
    public function adminTreeList()
    {
        $trees = Tree::with('species')->latest()->get(); 
        return view('admin.trees.index', compact('trees'));
    }

    public function adminTreeEdit(Tree $tree)
    {
        $species = Species::all(); 
        return view('admin.trees.edit', compact('tree', 'species'));
    }

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

        // --- 4. ADICIONADO LOG DE EDIÇÃO ---
        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_tree',
            'description' => 'O admin ' . Auth::user()->name . ' atualizou a árvore ' . $tree->species->name . ' (ID: ' . $tree->id . ').'
        ]);
        // --- FIM DO LOG ---

        return redirect()->route('admin.trees.index')->with('success', 'Árvore atualizada com sucesso!');
    }

    // --- 5. ADICIONADO MÉTODO DE DELETAR (E LOGAR) ---
    /**
     * [NOVO MÉTODO]
     * Remove uma árvore do banco de dados.
     */
    public function adminTreeDestroy(Tree $tree)
    {
        // Pega o nome da espécie ANTES de deletar, para usar no log
        $speciesName = $tree->species->name;
        $treeId = $tree->id;

        // Deleta a árvore
        $tree->delete();

        // Cria o log da ação
        AdminLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete_tree',
            'description' => 'O admin ' . Auth::user()->name . ' deletou a árvore ' . $speciesName . ' (ID: ' . $treeId . ').'
        ]);

        return redirect()->route('admin.trees.index')->with('success', 'Árvore deletada com sucesso!');
    }
}