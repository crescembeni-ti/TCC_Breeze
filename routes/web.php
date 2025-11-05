<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

// 1. IMPORTAR O NOVO CONTROLLER (Já estava correto)
use App\Http\Controllers\Auth\VerifyEmailCodeController; 

// --- ROTAS PÚBLICAS ---
Route::get('/', [TreeController::class, 'index'])->name('home');
Route::get('/api/trees', [TreeController::class, 'getTreesData'])->name('trees.data');
Route::get('/trees/{id}', [TreeController::class, 'show'])->name('trees.show');
Route::get('/sobre', [PageController::class, 'about'])->name('about');

// Rota de Denúncia/Reporte (já estava protegida, mantido)
Route::post('/contato/denuncia', [ReportController::class, 'store'])->middleware('auth')->name('report.store');

// --- GRUPO 1: AUTENTICADO (Apenas login) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- GRUPO 2: AUTENTICADO E VERIFICADO ---
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Rotas para FAZER a solicitação
    Route::get('/contato', [ContactController::class, 'index'])->name('contact');
    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

    // Rota para VER as solicitações
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestList'])
         ->name('contact.myrequests');
         
    // --- ADAPTAÇÃO 1: ADICIONADA A ROTA DE CANCELAMENTO ---
    Route::patch('/minhas-solicitacoes/{contact}/cancelar', [ContactController::class, 'cancelRequest'])
         ->name('contact.cancel');
});

// --- GRUPO 3: ROTAS DE ADMINISTRADOR ---
Route::middleware(['auth', 'admin'])->group(function () {
    // (O seu código de rotas de admin vem aqui)
    Route::get('/dashboard/map', [TreeController::class, 'adminMap'])->name('admin.map');
    Route::post('/dashboard/map', [TreeController::class, 'storeTree'])->name('admin.map.store');
    Route::get('/dashboard/trees', [TreeController::class, 'adminTreeList'])->name('admin.trees.index');
    Route::get('/dashboard/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('admin.trees.edit');
    Route::patch('/dashboard/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('admin.trees.update');
    Route::get('/dashboard/contacts', [ContactController::class, 'adminContactList'])->name('admin.contacts.index');
    Route::patch('/dashboard/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])->name('admin.contacts.updateStatus');
});

// 2. ROTAS DE VERIFICAÇÃO DE CÓDIGO (Já estavam corretas)
Route::get('/verify-code', [VerifyEmailCodeController::class, 'show'])->name('verification.code.notice');
Route::post('/verify-code', [VerifyEmailCodeController::class, 'verify'])->name('verification.code.verify');
Route::post('/resend-code', [VerifyEmailCodeController::class, 'resend'])->name('verification.code.resend');

// --- ADAPTAÇÃO 2: REMOVIDA A LINHA DE AGENDAMENTO DAQUI ---
// A linha abaixo (Schedule::command...) deve ficar no seu arquivo 'routes/console.php'

// Arquivo de rotas de autenticação do Breeze (login, registro, etc.)
require __DIR__.'/auth.php';