<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Models\ServiceOrder;


class ServiceExecutionController extends Controller
{
    public function index()
    {
       // Recupera todas as ordens de serviço atribuídas ao usuário autenticado
        $ordensDeServico = ServiceOrder::with('contact')  // Inclui dados relacionados ao contato
            ->where('supervisor_id', auth()->id())  // Filtra as ordens de serviço do usuário autenticado
            ->whereNull('data_execucao')  // Somente as ordens que ainda não foram executadas
            ->get();

        return view('servico.tarefas', compact('ordensDeServico'));
    }

    // Serviço concluiu
    public function concluir($id)
    {
       $os = ServiceOrder::findOrFail($id);

    // Marca a ordem de serviço como concluída, e registra a data de execução
    $os->data_execucao = now();
    $os->save();

    // Redireciona para a lista de ordens de serviço
    return redirect()->route('service.tasks.index');

        //return back()->with('success', 'Serviço concluído com sucesso.');
    }

    // Serviço não conseguiu fazer
    public function falha(Request $request, $id)
    {
        $os = ServiceOrder::findOrFail($id);

    // Marca a ordem de serviço como falha, e registra o motivo
    $os->motivos[] = $request->input('motivo_falha');
    $os->save();

    // Redireciona para a lista de ordens de serviço
    return redirect()->route('servico.dashboard');

        //return back()->with('warning', 'Falha registrada.');
    }

}
