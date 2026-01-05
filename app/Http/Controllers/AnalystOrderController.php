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
            'laudo_tecnico' => 'required|string|min:10',
            // Adicione validações para fotos ou outros dados técnicos aqui
        ]);

        $os->update([
            'laudo_tecnico' => $request->laudo_tecnico,
            // 'dados_extras' => $request->dados_extras,
            
            'status' => 'recebida' // <--- IMPORTANTE: Isso faz aparecer na aba 'Recebidas' do Admin
        ]);

        return redirect()->route('analista.os.index')->with('success', 'Laudo enviado ao Administrador!');
    }
}