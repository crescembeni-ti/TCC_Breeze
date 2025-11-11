<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailCodeController;
use App\Models\Bairro;

// ==============================
// 🌍 ROTAS PÚBLICAS
// ==============================
Route::get('/', [TreeController::class, 'index'])->name('home');
Route::get('/api/trees', [TreeController::class, 'getTreesData'])->name('trees.data');
Route::get('/trees/{id}', [TreeController::class, 'show'])->name('trees.show');
Route::get('/sobre', [PageController::class, 'about'])->name('about');

// 📣 Envio de denúncia/reporte (somente autenticado)
Route::post('/contato/denuncia', [ReportController::class, 'store'])
    ->middleware('auth')
    ->name('report.store');
Route::get('/bairros/data', function () {
    return response()->json(Bairro::all());
})->name('bairros.data');
// ==============================
// 👤 ROTAS DE USUÁRIO LOGADO (perfil, etc.)
// ==============================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==============================
// 🔐 ROTAS PROTEGIDAS (autenticado e verificado)
// ==============================

Route::middleware(['auth', 'verified'])->group(function () {

    // 🧭 DASHBOARD UNIFICADO
    // (exibe painel do admin ou do usuário conforme o campo is_admin)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ✉️ FAZER SOLICITAÇÃO
    Route::get('/contato', [ContactController::class, 'index'])->name('contact');
    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

    // 📋 MINHAS SOLICITAÇÕES
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestList'])
        ->name('contact.myrequests');

    // ❌ CANCELAR SOLICITAÇÃO
    Route::patch('/minhas-solicitacoes/{contact}/cancelar', [ContactController::class, 'cancelRequest'])
        ->name('contact.cancel');
});

// ==============================
// 🛠️ ROTAS DE ADMINISTRADOR
// ==============================
Route::middleware(['auth', 'admin'])->group(function () {

    // 🌍 Mapa administrativo
    Route::get('/dashboard/map', [TreeController::class, 'adminMap'])->name('admin.map');
    Route::post('/dashboard/map', [TreeController::class, 'storeTree'])->name('admin.map.store');

    // 🌲 Árvores
    Route::get('/dashboard/trees', [TreeController::class, 'adminTreeList'])->name('admin.trees.index');
    Route::get('/dashboard/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('admin.trees.edit');
    Route::patch('/dashboard/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('admin.trees.update');
    Route::delete('/dashboard/trees/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('admin.trees.destroy');
;

    // 💬 Mensagens / Contatos
    Route::get('/dashboard/contacts', [ContactController::class, 'adminContactList'])
    ->name('admin.contato.index');

    Route::patch('/dashboard/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])->name('admin.contacts.updateStatus');
});

// ==============================
// 📧 VERIFICAÇÃO DE CÓDIGO (EMAIL 2FA)
// ==============================
Route::get('/verify-code', [VerifyEmailCodeController::class, 'show'])->name('verification.code.notice');
Route::post('/verify-code', [VerifyEmailCodeController::class, 'verify'])->name('verification.code.verify');
Route::post('/resend-code', [VerifyEmailCodeController::class, 'resend'])->name('verification.code.resend');

// ==============================
// 🔑 ROTAS DE AUTENTICAÇÃO DO BREEZE
// ==============================
require __DIR__ . '/auth.php';
