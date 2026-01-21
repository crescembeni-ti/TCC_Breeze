<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id(); // Usa o ID direto da facade para garantir

        // -------------------------------------------------------
        // 1. BUSCA AS TAREFAS ATIVAS (IGUALZINHO À SUA LISTA)
        // -------------------------------------------------------
        $tarefasAtivas = ServiceOrder::with(['contact.status'])
            ->where('service_id', $userId)
            ->where('destino', 'servico') // Só o que está com a equipe agora
            ->get();

        // Agora filtramos no PHP (garante que bate com o visual)
        
        // Conta quantas tem o status "Vistoriado" (Pendentes)
        $pendentes = $tarefasAtivas->filter(function ($os) {
            return optional($os->contact->status)->name === 'Vistoriado';
        })->count();

        // Conta quantas tem o status "Em Execução"
        $emAndamento = $tarefasAtivas->filter(function ($os) {
            return optional($os->contact->status)->name === 'Em Execução';
        })->count();


        // -------------------------------------------------------
        // 2. BUSCA AS CONCLUÍDAS (QUERY SEPARADA)
        // -------------------------------------------------------
        // Como o seu controller de execução remove o 'destino' ao concluir,
        // precisamos buscar sem o filtro de destino, mas com status 'concluido'.
        $concluidas = ServiceOrder::where('service_id', $userId)
            ->where('status', 'concluido')
            ->count();

        // Pega o usuário para exibir o nome
        $user = Auth::user();

        // RETORNO (Verifique o nome da pasta: 'service' ou 'servico')
        // Se sua pasta for 'servico', mantenha abaixo. Se for 'service', mude.
        return view('servico.dashboard', compact('user', 'pendentes', 'emAndamento', 'concluidas'));
    }
}