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
    
    // --- Solicitações ---
    Route::post('/contato_com_anexo', [ContactController::class, 'storeApi']);
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestListApi']);
    Route::get('/admin/solicitacoes', [ContactController::class, 'adminRequestListApi']);
    
    // --- Usuário ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // =======================================================
    //  ROTA ADICIONADA: Para o App ENVIAR a foto de perfil
    // =======================================================
    Route::post('/user/photo', [AuthController::class, 'updatePhoto']);
});