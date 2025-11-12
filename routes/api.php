<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\ContactController; 
use App\Models\Status; // <-- 1. ADICIONE ESTE IMPORT

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Rotas Públicas ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// =======================================================
//  ROTA ADICIONADA: Para o app buscar os status (Ex: Em Análise, Concluído)
// =======================================================
Route::get('/statuses', function () {
    return response()->json(Status::all());
});


// --- Rotas Protegidas (Exigem Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Solicitações ---
    Route::post('/contato_com_anexo', [ContactController::class, 'storeApi']);
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestListApi']);
    Route::get('/admin/solicitacoes', [ContactController::class, 'adminRequestListApi']);
    
    // Rota para o Admin ATUALIZAR o status
    Route::post('/admin/solicitacoes/{contact}/status', [ContactController::class, 'adminUpdateStatusApi']);

    
    // --- Usuário ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/photo', [AuthController::class, 'updatePhoto']);
});