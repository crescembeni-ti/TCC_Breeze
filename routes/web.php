<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS - USUÁRIO
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\AboutPageController;

/*
|--------------------------------------------------------------------------
| CONTROLLERS - ADMIN
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminLoginController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\ServiceOrderController;

/*
|--------------------------------------------------------------------------
| CONTROLLERS - ANALISTA
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Analista\AuthenticatedSessionController as AnalystLoginController;

/*
|--------------------------------------------------------------------------
| CONTROLLERS - SERVIÇO
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Servico\AuthenticatedSessionController as ServiceLoginController;
use App\Http\Controllers\Servico\ServiceExecutionController;
use App\Http\Controllers\Servico\ServiceDashboardController;

/*
|--------------------------------------------------------------------------
| CONTROLLERS - ESPÉCIES
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\SpeciesController;

/*
|--------------------------------------------------------------------------
| CONTROLLERS - VERIFICAÇÃO
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Auth\VerifyEmailCodeController;
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

    Route::get('/sobre', [AboutPageController::class, 'index'])->name('about');

    Route::get('/bairros/data', fn () => response()->json(Bairro::all()))
        ->name('bairros.data');
});

/*
|--------------------------------------------------------------------------
| VERIFICAÇÃO DE E-MAIL
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/verification/code', [VerifyEmailCodeController::class, 'show'])
        ->name('verification.code.show');

    Route::post('/verification/code', [VerifyEmailCodeController::class, 'verify'])
        ->name('verification.code.verify');

    Route::post('/verification/code/resend', [VerifyEmailCodeController::class, 'resend'])
        ->name('verification.code.resend');
});

/*
|--------------------------------------------------------------------------
| DENÚNCIAS
|--------------------------------------------------------------------------
*/
Route::post('/contato/denuncia', [ReportController::class, 'store'])
    ->middleware(['auth:web', 'preventBack'])
    ->name('report.store');

/*
|--------------------------------------------------------------------------
| PERFIL USUÁRIO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'preventBack'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');
});

/*
|--------------------------------------------------------------------------
| USUÁRIO VERIFICADO
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
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-admin')->name('admin.')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.login'));

    Route::middleware(['guest:admin'])->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:admin', 'preventBack'])->group(function () {

        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', [TreeController::class, 'adminDashboard'])->name('dashboard');

        /*
        | PERFIL ADMIN
        */
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [AdminProfileController::class, 'updatePassword'])
            ->name('profile.password.update');

        /*
        | MAPA (sidebar usa admin.map)
        */
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map');
        Route::post('/map', [TreeController::class, 'storeTree'])->name('map.store');

        /*
        | ÁRVORES (corrigido p/ sidebar)
        */
        Route::prefix('trees')->name('trees.')->group(function () {
            Route::get('/', [TreeController::class, 'adminTreeList'])->name('index');
            Route::get('/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('edit');
            Route::patch('/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('update');
            Route::delete('/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('destroy');
        });

        /*
        | CONTATOS
        */
        Route::get('/contacts', [ContactController::class, 'adminContactList'])
            ->name('contato.index');

        Route::patch('/contacts/{contact}/forward', [ContactController::class, 'forward'])
            ->name('contacts.forward');

        /*
        | ORDENS DE SERVIÇO (OBJETIVO FINAL)
        */
        /*
        |--------------------------------------------------------------------------
        | ORDENS DE SERVIÇO – ADMIN (OBJETIVO FINAL)
        |--------------------------------------------------------------------------
        | • Apenas OS já enviadas
        | • Filtros: Analista | Serviço
        | • Ações: Ver | Cancelar envio
        */
        Route::get('/os', [AdminServiceController::class, 'index'])
            ->name('os.index');

        Route::get('/os/{os}', [AdminServiceController::class, 'show'])
            ->name('os.show');

        Route::patch('/os/{os}/cancelar', [AdminServiceController::class, 'cancelar'])
            ->name('os.cancelar');

    

        /*
        | NOTÍCIAS
        */
        Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');

        /*
        | CONTAS
        */
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [AccountManagementController::class, 'index'])->name('index');
        });
    });
});

/*
|--------------------------------------------------------------------------
| ANALISTA
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-analista')->name('analyst.')->group(function () {

    Route::get('/', fn () => redirect()->route('analyst.login'));

    Route::middleware(['guest:analyst'])->group(function () {
        Route::get('/login', [AnalystLoginController::class, 'create'])->name('login');
        Route::post('/login', [AnalystLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:analyst', 'preventBack'])->group(function () {

        Route::get('/dashboard', [ContactController::class, 'analystDashboard'])->name('dashboard');
        Route::get('/vistorias-pendentes', [ContactController::class, 'vistoriasPendentes'])
            ->name('vistorias.pendentes');
    });
});

/*
|--------------------------------------------------------------------------
| SERVIÇO
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-servico')->name('service.')->group(function () {

    Route::get('/', fn () => redirect()->route('service.login'));

    Route::middleware(['guest:service'])->group(function () {
        Route::get('/login', [ServiceLoginController::class, 'create'])->name('login');
        Route::post('/login', [ServiceLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:service', 'preventBack'])->group(function () {

        Route::get('/dashboard', [ServiceDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/tarefas', [ServiceExecutionController::class, 'index'])
            ->name('tasks.index');
    });
});

require __DIR__ . '/auth.php';
