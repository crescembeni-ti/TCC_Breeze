<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View; // Importe a View

// Não precisamos mais de AdminLog, Tree, Activity, ou Species aqui.
// Essa lógica agora pertence aos seus controllers de admin (ex: TreeController).

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard APENAS para usuários normais.
     * A lógica de admin foi movida para o grupo de rotas 'pbi-admin'.
     */
    public function index(): View
    {
        // 1. Pega o usuário 'web' (normal) que está logado.
        $user = Auth::user();

        // 2. MUDANÇA: Removemos toda a verificação 'is_admin'
        // e a busca por 'AdminLog' e 'stats'.
        // Este controller não é mais responsável por isso.
        
        // 3. Retorna a view 'dashboard' apenas com os dados do usuário.
        // Você precisará editar seu 'dashboard.blade.php' para
        // remover qualquer menção a '$adminLogs' e '$stats'.
        return view('dashboard', [
            'user' => $user,
        ]);
    }
}