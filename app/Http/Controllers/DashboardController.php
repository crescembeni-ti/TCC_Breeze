<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tree;
use App\Models\Activity;
use App\Models\Bairro;
use App\Models\Contact;
use App\Models\AdminLog;

/**
 * Controlador responsável pela inteligência de dados e painéis de controle (Dashboards).
 * Este controlador decide qual interface exibir para o usuário baseado em seu perfil 
 * (Cidadão, Admin ou Analista) e compila as estatísticas globais do sistema.
 */
class DashboardController extends Controller
{
    /**
     * Ponto de entrada central para o Dashboard.
     * 
     * Blocos de lógica:
     * - Estatísticas Globais: Compila números sobre árvores, solicitações pendentes, 
     *   atividades realizadas e diversidade de espécies para exibição em cards.
     * - Direcionamento de Perfil: Verifica se o usuário é administrativo (Admin/Analista) 
     *   ou um cidadão comum.
     * - Gestão de Logs (Admin): Se for administrador, processa os filtros de auditoria 
     *   (por ação ou período) para monitorar as alterações feitas no sistema.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Compilação de indicadores de performance (KPIs) do projeto
        $stats = [
            'total_trees' => Tree::count(),
            'total_requests' => Contact::whereHas('status', function ($query) {
                $query->where('name', 'Em Análise'); 
            })->count(),
            'total_species' => Tree::distinct('scientific_name')->count('scientific_name'),
        ];

        /* ============================================================
         * FLUXO 1: PAINEL ADMINISTRATIVO (ADMIN / ANALISTA)
         * ============================================================ */
        if (auth()->guard('admin')->check() || auth()->guard('analyst')->check()) {
            
            // Inicializa a query de auditoria com carregamento ansioso (Eager Loading) do admin
            $query = AdminLog::with('admin')->latest();

            // Filtro por tipo de ação realizada no sistema
            if ($request->filled('filter')) {
                $filter = $request->filter;
                if ($filter == 'cadastro') $query->where('action', 'like', '%create%');
                elseif ($filter == 'atualizacao') $query->where('action', 'like', '%update%');
                elseif ($filter == 'exclusao') $query->where('action', 'like', '%delete%');
                elseif ($filter == 'aprovacao') $query->where('action', 'like', '%approve%');
            }

            // Filtro cronológico para análise de produtividade
            if ($request->filled('period')) {
                $period = $request->period;
                if ($period == '7_days') $query->where('created_at', '>=', now()->subDays(7));
                elseif ($period == '30_days') $query->where('created_at', '>=', now()->subDays(30));
                elseif ($period == 'year') $query->where('created_at', '>=', now()->subYear());
            }

            // Paginação dos resultados mantendo os filtros na URL
            $adminLogs = $query->paginate(10)->appends($request->all());
            $recentActivities = null; 

            return view('admin.dashboard', compact('stats', 'adminLogs', 'recentActivities', 'user'));
        }

        /* ============================================================
         * FLUXO 2: PAINEL DO CIDADÃO (USUÁRIO COMUM)
         * ============================================================ */
        // Retorna a visão simplificada focada em informações gerais e ODS (Objetivos de Desenvolvimento Sustentável)
        return view('dashboard', compact('stats', 'user'));
    }
}
