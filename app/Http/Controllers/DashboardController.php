<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tree;
use App\Models\Activity;
use App\Models\Bairro;
use App\Models\Contact;
use App\Models\AdminLog; // Importante para os logs do admin

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Estatísticas Gerais (Comuns a todos)
        $stats = [
            'total_trees' => Tree::count(),
            'total_requests' => Contact::whereHas('status', function ($query) {
                $query->where('name', 'Em Análise'); 
            })->count(),
            'total_activities' => Activity::count(),
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'),
        ];

        // 1. SE FOR ADMIN OU ANALISTA -> Retorna o Painel Administrativo (com logs e filtros)
        if (auth()->guard('admin')->check() || auth()->guard('analyst')->check()) {
            
            // Lógica de Logs (Só precisa rodar se for admin)
            $query = AdminLog::with('admin')->latest();

            if ($request->filled('filter')) {
                $filter = $request->filter;
                if ($filter == 'cadastro') $query->where('action', 'like', '%create%');
                elseif ($filter == 'atualizacao') $query->where('action', 'like', '%update%');
                elseif ($filter == 'exclusao') $query->where('action', 'like', '%delete%');
                elseif ($filter == 'aprovacao') $query->where('action', 'like', '%approve%');
            }

            if ($request->filled('period')) {
                $period = $request->period;
                if ($period == '7_days') $query->where('created_at', '>=', now()->subDays(7));
                elseif ($period == '30_days') $query->where('created_at', '>=', now()->subDays(30));
                elseif ($period == 'year') $query->where('created_at', '>=', now()->subYear());
            }

            $adminLogs = $query->paginate(10)->appends($request->all());
            $recentActivities = null; // Admin usa logs, não activities recentes simples

            return view('admin.dashboard', compact('stats', 'adminLogs', 'recentActivities', 'user'));
        }

        // 2. SE FOR USUÁRIO COMUM -> Retorna o Painel do Usuário (Carrossel e ODS)
        // Aqui não precisamos de logs ou filtros avançados
        
        return view('dashboard', compact('stats', 'user')); // <--- AQUI CHAMA A VIEW DO USUÁRIO
    }
}