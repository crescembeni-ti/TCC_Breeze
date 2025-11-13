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

// ADMIN controllers
use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminLoginController;
use App\Http\Controllers\Admin\AdminProfileController;


/*
|--------------------------------------------------------------------------
| 🌍 ROTAS PÚBLICAS (com preventBack)
|--------------------------------------------------------------------------
*/
Route::middleware('preventBack')->group(function () {

    Route::get('/', [TreeController::class, 'index'])->name('home');

    Route::get('/api/trees', [TreeController::class, 'getTreesData'])->name('trees.data');
    Route::get('/trees/{id}', [TreeController::class, 'show'])->name('trees.show');

    Route::get('/sobre', [PageController::class, 'about'])->name('about');

    Route::get('/bairros/data', fn() => response()->json(Bairro::all()))
        ->name('bairros.data');
});


/*
|--------------------------------------------------------------------------
| 📣 Envio de denúncia (somente usuários logados)
|--------------------------------------------------------------------------
*/
Route::post('/contato/denuncia', [ReportController::class, 'store'])
    ->middleware(['auth', 'preventBack'])
    ->name('report.store');


/*
|--------------------------------------------------------------------------
| 👤 PERFIL DO USUÁRIO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'preventBack'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| 🟩 ÁREA DO USUÁRIO VERIFICADO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'preventBack'])->group(function () {

    // Após logar → ir para o MAPA
   Route::middleware(['auth', 'verified', 'preventBack'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});


    Route::get('/contato', [ContactController::class, 'index'])->name('contact');
    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestList'])
        ->name('contact.myrequests');

    Route::patch('/minhas-solicitacoes/{contact}/cancelar', [ContactController::class, 'cancelRequest'])
        ->name('contact.cancel');
});


/*
|--------------------------------------------------------------------------
| 🔐 ROTAS ADMINISTRATIVAS (/pbi-admin)
| Totalmente separadas
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-admin')->name('admin.')->group(function () {

    // /pbi-admin → login do admin
    Route::get('/', fn() => redirect()->route('admin.login'));

    /*
    |--------------------------------------------------------------------------
    | LOGIN DO ADMIN (guest:admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store']);
    });

    /*
    |--------------------------------------------------------------------------
    | ÁREA PROTEGIDA DO ADMIN (auth:admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:admin', 'preventBack'])->group(function () {

        // logout
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

        // após logar → ir para mapa admin
       Route::get('/dashboard', [TreeController::class, 'adminDashboard'])->name('dashboard');


        // mapa admin
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map');
        Route::post('/map', [TreeController::class, 'storeTree'])->name('map.store');

        // árvores
        Route::get('/trees', [TreeController::class, 'adminTreeList'])->name('trees.index');
        Route::get('/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('trees.edit');
        Route::patch('/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('trees.update');
        Route::delete('/trees/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('trees.destroy');

        // contatos
        Route::get('/contacts', [ContactController::class, 'adminContactList'])->name('contato.index');
        Route::patch('/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])
            ->name('contacts.updateStatus');

        // perfil admin
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
    });
});


/*
|--------------------------------------------------------------------------
| 🔑 ROTAS DE AUTENTICAÇÃO BREEZE
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
