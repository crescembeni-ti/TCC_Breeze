<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\ContactController; 
use App\Models\Status; 
use App\Models\Noticia;
use App\Http\Controllers\Api\ContatoApiController;
use App\Http\Controllers\Api\NoticiaApiController; // <-- 1. IMPORT ADICIONADO

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

// Rota pública para LER notícias
Route::get('/noticias', function () {
    return response()->json(Noticia::latest()->get()); 
});


// --- Rotas Protegidas (Exigem Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Solicitações (Contato) ---
    Route::post('/contato_com_anexo', [ContatoApiController::class, 'storeApi']);
    Route::get('/minhas-solicitacoes', [ContatoApiController::class, 'userRequestListApi']);
    Route::get('/admin/solicitacoes', [ContatoApiController::class, 'adminRequestListApi']);
    Route::post('/admin/solicitacoes/{contact}/status', [ContatoApiController::class, 'adminUpdateStatusApi']);
    Route::get('/admin/solicitacoes-por-status/{statusName}', [ContatoApiController::class, 'adminRequestListByStatusApi']);
    
    // Rota para listar analistas (Spinner)
    Route::get('/admin/analistas', [ContatoApiController::class, 'getAnalystsList']);
    // Rota para analista ver tarefas
    Route::get('/analista/tarefas', [ContatoApiController::class, 'analystAssignedListApi']);


    // --- Usuário ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/photo', [AuthController::class, 'updatePhoto']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::delete('/user', [AuthController::class, 'deleteAccount']);
    
    // Rota para salvar token FCM
    Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);

    // =======================================================
    //  2. ROTA ADICIONADA: Criar Notícia (Admin)
    // =======================================================
    Route::post('/noticias', [NoticiaApiController::class, 'store']);
});