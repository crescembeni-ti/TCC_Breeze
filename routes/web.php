<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

// 1. IMPORTAR O NOVO CONTROLLER
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

    Route::get('/contato', [ContactController::class, 'index'])->name('contact');
    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');
});

// --- GRUPO 3: ROTAS DE ADMINISTRADOR ---
Route::middleware(['auth', 'admin'])->group(function () {
    // ... (suas rotas de admin) ...
});

// 2. ADICIONAR AS NOVAS ROTAS DE VERIFICAÇÃO DE CÓDIGO
// Elas devem ser públicas (sem middleware)
Route::get('/verify-code', [VerifyEmailCodeController::class, 'show'])->name('verification.code.notice');
Route::post('/verify-code', [VerifyEmailCodeController::class, 'verify'])->name('verification.code.verify');
Route::post('/resend-code', [VerifyEmailCodeController::class, 'resend'])->name('verification.code.resend');

Schedule::command('app:prune-unverified-users')->daily();

// Arquivo de rotas de autenticação do Breeze (login, registro, etc.)
require __DIR__.'/auth.php';