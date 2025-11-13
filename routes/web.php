<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\VerifyEmailCodeController;
use Illuminate\Support\Facades\Route;
use App\Models\Bairro;

// ===== Admin Controllers =====
use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminLoginController;
use App\Http\Controllers\Admin\AdminProfileController;

/*
|--------------------------------------------------------------------------
| 🌍 ROTAS PÚBLICAS (Acesso livre)
|--------------------------------------------------------------------------
*/
Route::get('/', [TreeController::class, 'index'])->name('home');
Route::get('/api/trees', [TreeController::class, 'getTreesData'])->name('trees.data');
Route::get('/trees/{id}', [TreeController::class, 'show'])->name('trees.show');
Route::get('/sobre', [PageController::class, 'about'])->name('about');

// 📣 Envio de denúncia (somente usuários logados)
Route::post('/contato/denuncia', [ReportController::class, 'store'])
    ->middleware('auth')
    ->name('report.store');

// 📍 Dados de bairros (para filtros do mapa)
Route::get('/bairros/data', fn() => response()->json(Bairro::all()))->name('bairros.data');

/*
|--------------------------------------------------------------------------
| 👤 ROTAS DE PERFIL DO USUÁRIO LOGADO
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 🔐 ROTAS DE USUÁRIO VERIFICADO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/contato', [ContactController::class, 'index'])->name('contact');
    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestList'])->name('contact.myrequests');
    Route::patch('/minhas-solicitacoes/{contact}/cancelar', [ContactController::class, 'cancelRequest'])->name('contact.cancel');
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
    Route::delete('/admin/trees/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('admin.trees.destroy');

    // 💬 Mensagens / Contatos
    Route::get('/dashboard/contacts', [ContactController::class, 'adminContactList'])
    ->name('admin.contato.index');

    Route::patch('/dashboard/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])->name('admin.contacts.updateStatus');
});

/*
| 📧 VERIFICAÇÃO DE CÓDIGO (2FA SIMPLES)
|--------------------------------------------------------------------------
*/
Route::get('/verify-code', [VerifyEmailCodeController::class, 'show'])->name('verification.code.notice');
Route::post('/verify-code', [VerifyEmailCodeController::class, 'verify'])->name('verification.code.verify');
Route::post('/resend-code', [VerifyEmailCodeController::class, 'resend'])->name('verification.code.resend');

/*
|--------------------------------------------------------------------------
| 🛠️ ROTAS DO ADMINISTRADOR
|--------------------------------------------------------------------------
| Tudo dentro de /pbi-admin é separado dos usuários comuns.
| Usa o "guard" admin (autenticação independente)
*/
Route::prefix('pbi-admin')->name('admin.')->group(function () {

    // 🔁 Redireciona /pbi-admin → /pbi-admin/login
    Route::get('/', function () {
        return redirect()->route('admin.login');
    });

    // === LOGIN DO ADMIN ===
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store']);
    });

    // === ÁREA RESTRITA (auth:admin) ===
    Route::middleware('auth:admin')->group(function () {

        // 🚪 Logout
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

        // 🧭 Dashboard do admin
        Route::get('/dashboard', [TreeController::class, 'adminDashboard'])->name('dashboard');

        // 🌳 Cadastro de árvores (mapa)
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map');
        Route::post('/map', [TreeController::class, 'storeTree'])->name('map.store');

        // 🌲 Árvores
        Route::get('/trees', [TreeController::class, 'adminTreeList'])->name('trees.index');
        Route::get('/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('trees.edit');
        Route::patch('/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('trees.update');
        Route::delete('/trees/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('trees.destroy');

        // 💬 Solicitações
        Route::get('/contacts', [ContactController::class, 'adminContactList'])->name('contato.index');
        Route::patch('/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])->name('contacts.updateStatus');

        // 👤 Perfil do admin
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| 🔑 ROTAS DE AUTENTICAÇÃO PADRÃO (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';