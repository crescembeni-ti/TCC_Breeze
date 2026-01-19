<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tree;
use App\Models\Activity;
use App\Models\Bairro;
use App\Models\Contact; // <--- 1. IMPORTANTE: Adicione esta linha para usar o Model Contact

class DashboardController extends Controller
{
    public function index()
    {
        // Obtém o usuário logado
        $user = auth()->user();

        // Estatísticas
        $stats = [
            'total_trees' => Tree::count(),
            
            // 2. MUDANÇA AQUI: Adicione a chave 'total_requests' que a View está pedindo
            'total_requests' => Contact::count(), 
            
            // Mantém a contagem de atividades se quiser, ou remove se não usar mais
            'total_activities' => Activity::count(), 
            
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'),
        ];

        // Atividades recentes
        $recentActivities = Activity::with(['tree', 'user'])
            ->orderBy('activity_date', 'desc')
            ->take(5)
            ->get();

        // Bairros
        $bairros = Bairro::orderBy('nome', 'asc')->get();

        // Retorna a view
        return view('admin.dashboard', compact('stats', 'recentActivities', 'bairros', 'user'));
    }
}