<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminLog;  // Para o feed de atividades do Admin
use App\Models\Tree;       // Para as estatísticas
use App\Models\Activity;   // Para as estatísticas
use App\Models\Species;    // Para as estatísticas

class DashboardController extends Controller
{
    /**
     * Mostra o painel principal do dashboard.
     */
    public function index()
    {
        // 1. Pega os 10 logs mais recentes dos admins
        $adminLogs = AdminLog::with('user')
                                ->latest() // Ordena pelos mais novos
                                ->take(10)   // Limita a 10
                                ->get();

        // 2. Calcula as estatísticas (ex: totais)
        $stats = [
            'total_trees' => Tree::count(),
            'total_activities' => Activity::count(),
            'total_species' => Species::count(),
        ];

        // 3. Envia os logs e as estatísticas para a view
        return view('dashboard', [
            'adminLogs' => $adminLogs,
            'stats' => $stats
        ]);
    }
}