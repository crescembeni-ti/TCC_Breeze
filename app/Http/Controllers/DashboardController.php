<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Tree;
use App\Models\Activity;
use App\Models\Species;
use App\Models\Bairro;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard apenas para usuários autenticados.
     */
    public function index(): View|RedirectResponse
    {
        // Verifica se há usuário logado
        if (!Auth::check()) {
            // Se não estiver logado, redireciona para o login
            return redirect()->route('login');
        }

        // Obtém o usuário autenticado
        $user = Auth::user();

        // Estatísticas
        $stats = [
            'total_trees' => Tree::count(),
            'total_activities' => Activity::count(),
            'total_species' => Species::count(),
        ];

        // Atividades recentes
        $recentActivities = Activity::with(['tree.species', 'user'])
            ->orderBy('activity_date', 'desc')
            ->take(5)
            ->get();

        // Bairros
        $bairros = Bairro::orderBy('nome', 'asc')->get();

        // Retorna a view
        return view('dashboard', compact('user', 'stats', 'recentActivities', 'bairros'));
    }
}
