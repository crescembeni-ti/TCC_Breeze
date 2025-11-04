<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContatoApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar as rotas de API para sua aplicação.
| Estas rotas são carregadas pelo RouteServiceProvider.
|
*/

// Rota pública para o App fazer Login
Route::post('/login', [AuthController::class, 'login']);

// Rotas Protegidas:
// O app só pode acessar aqui se enviar o Token (a "chave")
Route::middleware('auth:sanctum')->group(function () {
    
    // Rota para o App enviar o formulário de contato
    Route::post('/contato', [ContatoApiController::class, 'store']);
    
    // Rota bônus para o App pegar os dados do usuário logado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});