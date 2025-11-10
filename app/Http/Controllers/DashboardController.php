<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\AdminLog;
use App\Models\Tree;
use App\Models\Activity;
use App\Models\Species;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Se for admin, carrega logs e estatísticas
        $adminLogs = [];
        $stats = [];

        if ($user->is_admin) {
            $adminLogs = AdminLog::with('user')
                ->latest()
                ->take(10)
                ->get();

            $stats = [
                'total_trees' => Tree::count(),
                'total_activities' => Activity::count(),
                'total_species' => Species::count(),
            ];
        }

        // Retorna a mesma view para todos
        return view('dashboard', [
            'user' => $user,
            'adminLogs' => $adminLogs,
            'stats' => $stats,
        ]);
    }
}
