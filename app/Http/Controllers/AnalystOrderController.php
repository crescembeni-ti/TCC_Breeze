<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceOrder;
use App\Models\Analyst;
use Illuminate\Support\Facades\Auth;

class AnalystOrderController extends Controller
{
    // LISTA AS ORDENS PENDENTES PARA O ANALISTA LOGADO
    public function index()
    {
        $userEmail = Auth::user()->email; 
        // Busca o ID do analista comparando o email do usuário logado
        // (Ajuste essa lógica se você usa user_id na tabela analysts)
        $analyst = Analyst::where('email', $userEmail)->first(); 

        if (!$analyst) {
            return back()->with('error', 'Perfil de analista não encontrado para este usuário.');
        }

        // Filtra apenas as que estão 'enviada' para este analista
        $oss = ServiceOrder::where('analyst_id', $analyst->id)
                    ->where('status', 'enviada')
                    ->with('contact')
                    ->latest()
                    ->get();

        return view('analyst.index', compact('oss'));
    }

    // FORMULÁRIO PARA PREENCHER O LAUDO
    public function edit($id)
    {
        $os = ServiceOrder::with('contact')->findOrFail($id);
        return view('analyst.edit', compact('os'));
    }

    // SALVA E DEVOLVE PARA O ADMIN (Muda status para 'recebida')
    public function update(Request $request, $id)
{
        $os = ServiceOrder::findOrFail($id);

        $request->validate([
            'laudo_tecnico' => 'required|string|min:5', // Ajuste a validação conforme precisar
        ]);

        // 1. Atualiza a OS com o laudo
        $os->update([
            'laudo_tecnico' => $request->laudo_tecnico,
            'status' => 'analise_concluida', // Status interno da OS
            'flow' => null // Remove do painel do analista
        ]);

        // 2. ATUALIZA O CONTATO PARA "VISTORIADO" (CRUCIAL PARA A PARTE 2)
        // Isso faz ele reaparecer na lista de solicitações do Admin
        $statusVistoriado = \App\Models\Status::where('name', 'Vistoriado')->first();
        
        if ($statusVistoriado) {
            $os->contact->update([
                'status_id' => $statusVistoriado->id
            ]);
        }

        return redirect()->route('analyst.dashboard')
            ->with('success', 'Laudo enviado! Solicitação retornada ao Admin como Vistoriado.');
    }
}