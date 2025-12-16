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
     * DADOS DO MAPA
     * ============================================================ */
    public function getTreesData()
    {
        return Tree::with(['species', 'bairro'])->get()->map(fn ($tree) => [
            'id' => $tree->id,
            'latitude' => (float) $tree->latitude,
            'longitude' => (float) $tree->longitude,
            'species_name' => $tree->species->name,
            'color_code' => $tree->species->color_code,
            'address' => $tree->address,
            'bairro_id' => $tree->bairro_id,
            'bairro_nome' => $tree->bairro->nome ?? null,
            'health_status' => $tree->health_status,
            'trunk_diameter' => $tree->trunk_diameter,
        ]);
    }

    /* ============================================================
     * VISUALIZAÇÃO
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
        return view('admin.dashboard', [
            'stats' => [
                'total_trees' => Tree::count(),
                'total_activities' => Activity::count(),
                'total_species' => Species::count(),
            ],
            'adminLogs' => AdminLog::latest()->take(10)->get(),
        ]);
    }

    /* ============================================================
     * MAPA ADMIN
     * ============================================================ */
    public function adminMap()
    {
        return view('admin.trees.map', [
            'species' => Species::orderBy('name')->get(),
            'trees' => Tree::with(['species', 'bairro'])->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
        ]);
    }

    /* ============================================================
     * CADASTRAR ÁRVORE (SELECT + NOVA ESPÉCIE)
     * ============================================================ */
    public function storeTree(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

            // espécie
            'species_id' => 'nullable|exists:species,id',
            'new_species_name' => 'nullable|string|max:255',

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
            'description' => 'nullable|string|max:1000',
        ]);

        // resolve espécie
        if ($validated['species_id']) {
            $speciesId = $validated['species_id'];
        } else {
            $species = Species::firstOrCreate(
                ['name' => $validated['new_species_name']],
                [
                    'color_code' => '#' . substr(md5($validated['new_species_name']), 0, 6),
                    'description' => 'Cadastrada via mapa admin',
                ]
            );
            $speciesId = $species->id;
        }

        $tree = Tree::create(array_merge(
            collect($validated)->except(['species_id', 'new_species_name'])->toArray(),
            ['species_id' => $speciesId]
        ));

        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'create_tree',
            'description' => 'Árvore criada (ID ' . $tree->id . ')',
        ]);

        return redirect()->route('admin.map')->with('success', 'Árvore cadastrada!');
    }

    /* ============================================================
     * LISTA
     * ============================================================ */
    public function adminTreeList()
    {
        return view('admin.trees.index', [
            'trees' => Tree::with('species')->latest()->get(),
        ]);
    }

    /* ============================================================
     * EDITAR
     * ============================================================ */
    public function adminTreeEdit(Tree $tree)
    {
        return view('admin.trees.edit', [
            'tree' => $tree,
            'species' => Species::orderBy('name')->get(),
            'bairros' => Bairro::orderBy('nome')->get(),
        ]);
    }

    /* ============================================================
     * ATUALIZAR (TODOS OS CAMPOS)
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
            'target' => 'required|string|max:255',
            'injuries' => 'required|string|max:255',
            'wiring_status' => 'required|string|max:100',
            'total_width' => 'required|numeric|min:0',
            'street_width' => 'required|numeric|min:0',
            'gutter_height' => 'required|numeric|min:0',
            'gutter_width' => 'required|numeric|min:0',
            'gutter_length' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $tree->update($validated);

        AdminLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => 'update_tree',
            'description' => 'Árvore atualizada (ID ' . $tree->id . ')',
        ]);

        return redirect()->route('admin.trees.index')
            ->with('success', 'Árvore atualizada!');
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
}
