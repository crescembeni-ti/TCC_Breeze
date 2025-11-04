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

    // --- ADAPTAÇÃO 1: ADICIONADA A ROTA PARA O USUÁRIO VER AS SOLICITAÇÕES DELE ---
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestList'])
         ->name('contact.myrequests');
});

// --- GRUPO 3: ROTAS DE ADMINISTRADOR ---
Route::middleware(['auth', 'admin'])->group(function () {
    // ... (suas rotas de admin, como admin.contacts.index e admin.contacts.updateStatus) ...
});

// 2. ROTAS DE VERIFICAÇÃO DE CÓDIGO (Já estavam corretas)
// Elas devem ser públicas (sem middleware)
Route::get('/verify-code', [VerifyEmailCodeController::class, 'show'])->name('verification.code.notice');
Route::post('/verify-code', [VerifyEmailCodeController::class, 'verify'])->name('verification.code.verify');
Route::post('/resend-code', [VerifyEmailCodeController::class, 'resend'])->name('verification.code.resend');

// --- ADAPTAÇÃO 2: REMOVIDA A LINHA DE AGENDAMENTO DAQUI ---
// A linha abaixo (Schedule::command...) deve ficar no seu arquivo 'routes/console.php'
// Schedule::command('app:prune-unverified-users')->daily();

// Arquivo de rotas de autenticação do Breeze (login, registro, etc.)
require __DIR__.'/auth.php';
