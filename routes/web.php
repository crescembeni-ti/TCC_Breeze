<?php

use Illuminate\Support\Facades\Route;

// Controllers Usuário
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoticiaController;

// Controllers Admin
use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminLoginController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AccountManagementController;

// Controllers Analista
use App\Http\Controllers\Analista\AuthenticatedSessionController as AnalystLoginController;

// Controllers Serviço
use App\Http\Controllers\Servico\AuthenticatedSessionController as ServiceLoginController;

use App\Models\Bairro;


/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::middleware('preventBack')->group(function () {
    
    Route::get('/', [TreeController::class, 'index'])->name('home');

    Route::get('/api/trees', [TreeController::class, 'getTreesData'])->name('trees.data');

    Route::get('/trees/{id}', [TreeController::class, 'show'])->name('trees.show');

    Route::get('/sobre', [PageController::class, 'about'])->name('about');

    Route::get('/bairros/data', fn() => response()->json(Bairro::all()))->name('bairros.data');

});


/*
|--------------------------------------------------------------------------
| DENÚNCIAS (somente usuários logados)
|--------------------------------------------------------------------------
*/
Route::post('/contato/denuncia', [ReportController::class, 'store'])
    ->middleware(['auth:web', 'preventBack'])
    ->name('report.store');


/*
|--------------------------------------------------------------------------
| PERFIL DO USUÁRIO COMUM
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'preventBack'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


/*
|--------------------------------------------------------------------------
| ÁREA DO USUÁRIO VERIFICADO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'verified', 'preventBack'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/contato', [ContactController::class, 'index'])->name('contact');

    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestList'])
        ->name('contact.myrequests');

    Route::patch('/minhas-solicitacoes/{contact}/cancelar', [ContactController::class, 'cancelRequest'])
        ->name('contact.cancel');

});



/*
|--------------------------------------------------------------------------
| ÁREA ADMINISTRATIVA (/pbi-admin)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-admin')->name('admin.')->group(function () {

    Route::get('/', fn() => redirect()->route('admin.login'));

    // Login admin
    Route::middleware(['guest:admin', 'guard.only:admin'])->group(function () {

        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');

        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });


    /*
    |--------------------------------------------------------------------------
    | Área PROTEGIDA do Admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:admin', 'preventBack'])->group(function () {

        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', [TreeController::class, 'adminDashboard'])->name('dashboard');


        // ============================================================
        // Árvores
        // ============================================================
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map');

        Route::post('/map', [TreeController::class, 'storeTree'])->name('map.store');

        Route::get('/trees', [TreeController::class, 'adminTreeList'])->name('trees.index');

        Route::get('/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('trees.edit');

        Route::patch('/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('trees.update');

        Route::delete('/trees/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('trees.destroy');


        // ============================================================
        // Contatos
        // ============================================================
        Route::get('/contacts', [ContactController::class, 'adminContactList'])->name('contato.index');

        Route::patch('/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])->name('contacts.updateStatus');


        // ============================================================
        // Notícias
        // ============================================================
        Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');

        Route::get('/noticias/create', [NoticiaController::class, 'create'])->name('noticias.create');

        Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');


        // ============================================================
        // Perfil Admin
        // ============================================================
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');

        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');



        /*
        |--------------------------------------------------------------------------
        | ⭐ NOVO PAINEL DE CONTAS (Estilo Discord, com abas)
        |--------------------------------------------------------------------------
        | A rota index centraliza Admin, Analista e Serviço em um único painel.
        */
        Route::prefix('accounts')->name('accounts.')->group(function () {

            // Página única com as abas
            Route::get('/', [AccountManagementController::class, 'index'])->name('index');

            // Criar (via modal)
            Route::post('/store', [AccountManagementController::class, 'store'])->name('store');

            // Editar (via modal)
            Route::put('/update/{type}/{id}', [AccountManagementController::class, 'update'])->name('update');

            // Excluir
            Route::delete('/delete/{type}/{id}', [AccountManagementController::class, 'destroy'])->name('destroy');
        });

    });
});



/*
|--------------------------------------------------------------------------
| ÁREA DO ANALISTA (/pbi-analista)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-analista')->name('analyst.')->group(function () {

    Route::get('/', fn() => redirect()->route('analyst.login'));

    Route::middleware(['guest:analyst', 'guard.only:analyst'])->group(function () {

        Route::get('/login', [AnalystLoginController::class, 'create'])->name('login');

        Route::post('/login', [AnalystLoginController::class, 'store'])->name('login.store');

    });


    Route::middleware(['auth:analyst', 'preventBack'])->group(function () {

        Route::post('/logout', [AnalystLoginController::class, 'destroy'])->name('logout');

       Route::get('/dashboard', [ContactController::class, 'analystDashboard'])
     ->name('dashboard');

        Route::get('/vistorias-pendentes', [ContactController::class, 'vistoriasPendentes'])
            ->name('vistorias.pendentes');

        Route::get('/profile', fn() => view('analista.profile'))->name('profile.edit');
    });

});



/*
|--------------------------------------------------------------------------
| ÁREA DO SERVIÇO (/pbi-servico)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-servico')->name('service.')->group(function () {

    Route::get('/', fn() => redirect()->route('service.login'));

    Route::middleware(['guest:service', 'guard.only:service'])->group(function () {

        Route::get('/login', [ServiceLoginController::class, 'create'])->name('login');

        Route::post('/login', [ServiceLoginController::class, 'store'])->name('login.store');

    });

    Route::middleware(['auth:service', 'preventBack'])->group(function () {

        Route::post('/logout', [ServiceLoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', fn() => view('servico.dashboard'))->name('dashboard');

        Route::get('/tarefas', fn() => view('servico.tarefas'))->name('tasks.index');

        Route::get('/profile', fn() => view('servico.profile'))->name('profile.edit');
    });
});



/*
|--------------------------------------------------------------------------
| ROTAS PADRÃO LARAVEL BREEZE
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
