<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tree;
use App\Models\Activity;
use App\Models\Bairro;
// use App\Models\Species; // <--- REMOVIDO: A tabela não existe mais

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Obtém o usuário logado (CORREÇÃO AQUI)
        $user = auth()->user();

        // 2. Estatísticas
        $stats = [
            'total_trees' => Tree::count(),
            'total_activities' => Activity::count(),
            
            // Conta nomes científicos únicos na tabela trees
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'),
        ];

        // 3. Atividades recentes (Sem 'species')
        $recentActivities = Activity::with(['tree', 'user'])
            ->orderBy('activity_date', 'desc')
            ->take(5)
            ->get();

        // 4. Bairros
        $bairros = Bairro::orderBy('nome', 'asc')->get();

        // 5. Retorna a view enviando o $user junto
        return view('dashboard', compact('stats', 'recentActivities', 'bairros', 'user'));
    }
}