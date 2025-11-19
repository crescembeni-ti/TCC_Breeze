<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\ContactController; 
use App\Models\Status; 
use App\Models\Noticia;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Rotas Públicas ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/statuses', function () {
    return response()->json(Status::all());
});

Route::get('/noticias', function () {
    return response()->json(Noticia::latest()->get()); // Retorna todas, mais novas primeiro
});


// --- Rotas Protegidas (Exigem Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Solicitações ---
    Route::post('/contato_com_anexo', [ContactController::class, 'storeApi']);
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestListApi']);
    Route::get('/admin/solicitacoes', [ContactController::class, 'adminRequestListApi']);
    Route::post('/admin/solicitacoes/{contact}/status', [ContactController::class, 'adminUpdateStatusApi']);
    Route::get('/admin/solicitacoes-por-status/{statusName}', [ContactController::class, 'adminRequestListByStatusApi']);

    
    // --- Usuário ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/photo', [AuthController::class, 'updatePhoto']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // =======================================================
    //  ROTA ADICIONADA: Para o App salvar o token FCM
    // =======================================================
    Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
});