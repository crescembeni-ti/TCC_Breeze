<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // Controller de Login (Correto)
use App\Http\Controllers\ContactController; 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rota pública para o App fazer Login
Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);


// Rotas Protegidas:
Route::middleware('auth:sanctum')->group(function () {
    
    // Rota para o App ENVIAR a solicitação com anexo
    Route::post('/contato_com_anexo', [ContactController::class, 'storeApi']);
    
    // =======================================================
    //  ROTA ADICIONADA: Para o App BUSCAR as solicitações
    // =======================================================
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestListApi']);
    
    // Rota para o Admin BUSCAR TODAS as solicitações
    Route::get('/admin/solicitacoes', [ContactController::class, 'adminRequestListApi']);
    
    // Rota bônus para o App pegar os dados do usuário logado (Correto)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});