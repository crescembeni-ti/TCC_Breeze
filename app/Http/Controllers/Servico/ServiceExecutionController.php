<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('service')->user();

        // 1. TAREFAS PENDENTES
        // Lógica: Pertence ao usuário, destino é 'servico' E o status do contato é 'Vistoriado'
        $pendentes = ServiceOrder::where('service_id', $user->id)
            ->where('destino', 'servico')
            ->whereHas('contact.status', function (Builder $query) {
                $query->where('name', 'Vistoriado');
            })
            ->count();

        // 2. EM ANDAMENTO
        // Lógica: Pertence ao usuário, destino é 'servico' E o status do contato é 'Em Execução'
        $emAndamento = ServiceOrder::where('service_id', $user->id)
            ->where('destino', 'servico')
            ->whereHas('contact.status', function (Builder $query) {
                $query->where('name', 'Em Execução');
            })
            ->count();

        // 3. CONCLUÍDAS
        // Lógica: No seu controller, quando conclui, você define 'destino' => null e 'status' => 'concluido'.
        // Por isso, aqui NÃO filtramos por 'destino', apenas pelo status da OS.
        $concluidas = ServiceOrder::where('service_id', $user->id)
            ->where('status', 'concluido')
            ->count();

        // Se a pasta da view for 'servico' (português), mantenha assim. 
        // Se for 'service' (inglês), mude para 'service.dashboard'
        return view('servico.dashboard', compact('user', 'pendentes', 'emAndamento', 'concluidas'));
    }
}