<?php

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Models\Species;
use App\Models\Activity;
use App\Models\AdminLog;
use App\Models\Bairro;
use Illuminate\Http\Request;

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
     * API PÚBLICA DO MAPA
     * ============================================================ */
    public function getTreesData()
    {
        return Tree::with('species')->get()->map(function ($tree) {
            return [
                'id' => $tree->id,
                'latitude' => floatval($tree->latitude),
                'longitude' => floatval($tree->longitude),
                'species_name' => $tree->species->name,
                'color_code' => $tree->species->color_code,
                'address' => $tree->address,
                'health_status' => $tree->health_status,
            ];
        });
    }


    /* ============================================================
     * VISUALIZAÇÃO DE ÁRVORE
     * ============================================================ */
    public function show($id)
    {
        $tree = Tree::with(['species', 'user', 'activities.user'])->findOrFail($id);
        return view('trees.show', compact('tree'));
    }


    /* ============================================================
     * DASHBOARD ADMIN
     * ============================================================ */
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


    /* ============================================================
     * MAPA DO ADMIN
     * ============================================================ */
    public function adminMap()
    {
        $species = Species::all();
        $trees = Tree::with('species')->get();

        return view('admin.trees.map', compact('species', 'trees'));
    }


    /* ============================================================
     * CADASTRAR ÁRVORE
     * ============================================================ */
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

            // Novos campos completos
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

        // Verifica espécie
        $species = Species::firstOrCreate(
            ['name' => $validated['species_name']],
            [
                'description' => 'Espécie cadastrada pelo admin',
                'color_code' => '#' . substr(md5($validated['species_name']), 0, 6)
            ]
        );

        // Cria árvore
        $tree = Tree::create(array_merge($validated, [
            'species_id' => $species->id,
            'user_id' => auth('web')->check() ? auth('web')->id() : null,
            'address' => $validated['address'] ?? $validated['name']
        ]));

        // Log (sem user_id)
        AdminLog::create([
            'action' => 'create_tree',
            'description' => 'O admin ' . auth('admin')->user()->name .
                ' cadastrou a árvore: ' . $species->name . ' (ID: ' . $tree->id . ')'
        ]);

        return redirect()->route('admin.dashboard')
                         ->with('success', 'Árvore cadastrada com sucesso!');
    }


    /* ============================================================
     * LISTA ADMIN
     * ============================================================ */
    public function adminTreeList()
    {
        $trees = Tree::with('species')->latest()->get();
        return view('admin.trees.index', compact('trees'));
    }


    /* ============================================================
     * EDITAR ÁRVORE
     * ============================================================ */
    public function adminTreeEdit(Tree $tree)
    {
        $species = Species::all();
        return view('admin.trees.edit', compact('tree', 'species'));
    }


    /* ============================================================
     * ATUALIZAR (EDITAR) ÁRVORE
     * ============================================================ */
    public function adminTreeUpdate(Request $request, Tree $tree)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'species_id' => 'required|exists:species,id',
            'health_status' => 'required|in:good,fair,poor',
            'planted_at' => 'required|date',
            'address' => 'nullable|string|max:255',
            'trunk_diameter' => 'nullable|numeric|min:0',
        ]);

        $tree->update($validated);

        AdminLog::create([
            'action' => 'update_tree',
            'description' => 'O admin ' . auth('admin')->user()->name .
                ' atualizou a árvore ' . $tree->species->name .
                ' (ID: ' . $tree->id . ')'
        ]);

        return redirect()->route('admin.trees.index')
            ->with('success', 'Árvore atualizada!');
    }


    /* ============================================================
     * EXCLUIR ÁRVORE
     * ============================================================ */
    public function adminTreeDestroy(Tree $tree)
    {
        $name = $tree->species->name;
        $id = $tree->id;

        $tree->delete();

        AdminLog::create([
            'action' => 'delete_tree',
            'description' =>
                'O admin ' . auth('admin')->user()->name .
                " deletou a árvore $name (ID: $id)"
        ]);

        return redirect()->route('admin.trees.index')
            ->with('success', 'Árvore excluída!');
    }
}
