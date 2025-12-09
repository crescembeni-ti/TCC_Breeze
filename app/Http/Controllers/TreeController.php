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
     * BOLINHAS NO MAPA
     * ============================================================ */
    public function getTreesData()
    {
        $trees = Tree::with(['species', 'bairro'])->get()->map(function ($tree) {
            return [
                'id' => $tree->id,
                'latitude' => (float) $tree->latitude,
                'longitude' => (float) $tree->longitude,
                'species_name' => $tree->species->name,
                'color_code' => $tree->species->color_code,
                'address' => $tree->address,
                'bairro_id' => $tree->bairro_id, // ADICIONADO
                'bairro_nome' => $tree->bairro->nome ?? null, // ADICIONADO
                'health_status' => $tree->health_status,
                'trunk_diameter' => $tree->trunk_diameter,
            ];
        });

        return response()->json($trees);
    }


    /* ============================================================
     * VISUALIZAÇÃO DE ÁRVORE
     * ============================================================ */
    public function show($id)
    {
        $tree = Tree::with(['species', 'activities.user'])->findOrFail($id);
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
        $trees = Tree::with(['species', 'bairro'])->get();
        $bairros = Bairro::orderBy('nome')->get(); // ADICIONADO

        return view('admin.trees.map', compact('species', 'trees', 'bairros'));
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
        'planted_at' => 'required|date|before_or_equal:today',
        'trunk_diameter' => 'required|numeric|min:0',
        'address' => 'required|string|max:255',
        'bairro_id' => 'required|exists:bairros,id',
        'vulgar_name' => 'required|string|max:255',
        'scientific_name' => 'required|string|max:255',
        'cap' => 'required|numeric|min:0',
        'height' => 'required|numeric|min:0',
        'crown_height' => 'required|numeric|min:0',
        'crown_diameter_longitudinal' => 'required|numeric|min:0',
        'crown_diameter_perpendicular' => 'required|numeric|min:0',
        'bifurcation_type' => 'required|string|max:100',
        'stem_balance' => 'required|string|max:100',
        'crown_balance' => 'required|string|max:100',
        'organisms' => 'required|string|max:255',
        'target' => 'required|string|max:255',
        'injuries' => 'required|string|max:255',
        'wiring_status' => 'required|string|max:100',
        'total_width' => 'required|numeric|min:0',
        'street_width' => 'required|numeric|min:0',
        'gutter_height' => 'required|numeric|min:0',
        'gutter_width' => 'required|numeric|min:0',
        'gutter_length' => 'required|numeric|min:0',

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
            'bairro_id' => $validated['bairro_id'], // ADICIONADO
            'address' => $validated['address'] ?? $validated['name']
         ]));


        // Log corrigido
        AdminLog::create([
            'admin_id' => auth('admin')->id(),
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
        $bairros = Bairro::orderBy('nome')->get(); // ADICIONADO

        return view('admin.trees.edit', compact('tree', 'species', 'bairros'));
    }


    /* ============================================================
     * ATUALIZAR (EDITAR)
     * ============================================================ */
    public function adminTreeUpdate(Request $request, Tree $tree)
    {
        $validated = $request->validate([
            'species_id' => 'required|exists:species,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'health_status' => 'required|in:good,fair,poor',
            'planted_at' => 'required|date|before_or_equal:today',
            'trunk_diameter' => 'required|numeric|min:0',
            'address' => 'required|string|max:255',
            'bairro_id' => 'required|exists:bairros,id',
            'vulgar_name' => 'required|string|max:255',
            'scientific_name' => 'required|string|max:255',
            'cap' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'crown_height' => 'required|numeric|min:0',
            'crown_diameter_longitudinal' => 'required|numeric|min:0',
            'crown_diameter_perpendicular' => 'required|numeric|min:0',
            'bifurcation_type' => 'required|string|max:100',
            'stem_balance' => 'required|string|max:100',
            'crown_balance' => 'required|string|max:100',
            'organisms' => 'required|string|max:255',
            'target' => 'require|string|max:255',
            'injuries' => 'required|string|max:255',
            'wiring_status' => 'required|string|max:100',
            'total_width' => 'required|numeric|min:0',
            'street_width' => 'required|numeric|min:0',
            'gutter_height' => 'required|numeric|min:0',
            'gutter_width' => 'required|numeric|min:0',
            'gutter_length' => 'required|numeric|min:0',

            'no_species_case' => 'nullable|string|max:255',
        ]);

        $tree->update($validated);

        // Log corrigido
        AdminLog::create([
            'admin_id' => auth('admin')->id(),
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

        // Log corrigido
        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'delete_tree',
            'description' => 'O admin ' . auth('admin')->user()->name .
                " deletou a árvore $name (ID: $id)"
        ]);

        return redirect()->route('admin.trees.index')
            ->with('success', 'Árvore excluída!');
    }
}
