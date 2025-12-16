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
| CONTROLLERS - ESPÉCIES (ADICIONADO)
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
| VERIFICAÇÃO DE E-MAIL POR CÓDIGO
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
| DENÚNCIAS (USER LOGADO)
|--------------------------------------------------------------------------
*/
Route::post('/contato/denuncia', [ReportController::class, 'store'])
    ->middleware(['auth:web', 'preventBack'])
    ->name('report.store');

/*
|--------------------------------------------------------------------------
| PERFIL DO USUÁRIO
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
| ADMIN (/pbi-admin)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-admin')->name('admin.')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.login'));

    /*
    |--------------------------------------------------------------------------
    | LOGIN ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['guest:admin', 'guard.only:admin'])->group(function () {

        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    /*
    |--------------------------------------------------------------------------
    | ÁREA AUTENTICADA ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:admin', 'preventBack'])->group(function () {

        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', [TreeController::class, 'adminDashboard'])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | PERFIL ADMIN
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');

        Route::patch('/profile/password', [AdminProfileController::class, 'updatePassword'])
            ->name('profile.password.update');

        /*
        |--------------------------------------------------------------------------
        | ESPÉCIES (AJAX / MODAL)  ✅ ADICIONADO
        |--------------------------------------------------------------------------
        */
        Route::post('/species', [SpeciesController::class, 'store'])
            ->name('species.store');

        /*
        |--------------------------------------------------------------------------
        | ÁRVORES
        |--------------------------------------------------------------------------
        */
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map');
        Route::post('/map', [TreeController::class, 'storeTree'])->name('map.store');

        Route::get('/trees', [TreeController::class, 'adminTreeList'])->name('trees.index');
        Route::get('/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('trees.edit');
        Route::patch('/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('trees.update');
        Route::delete('/trees/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('trees.destroy');

        /*
        |--------------------------------------------------------------------------
        | CONTATOS
        |--------------------------------------------------------------------------
        */
        Route::get('/contacts', [ContactController::class, 'adminContactList'])
            ->name('contato.index');

        Route::patch('/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])
            ->name('contacts.updateStatus');

        Route::patch('/contacts/{contact}/forward', [ContactController::class, 'forward'])
            ->name('contacts.forward');

        /*
        |--------------------------------------------------------------------------
        | ORDENS DE SERVIÇO
        |--------------------------------------------------------------------------
        */
        Route::get('/os', [ServiceOrderController::class, 'index'])->name('os.index');
        Route::get('/os/{id}', [ServiceOrderController::class, 'show'])->name('os.show');

        Route::get('/os/pendentes', [AdminServiceController::class, 'ordensPendentes'])
            ->name('os.pendentes');

        Route::post('/os/{id}/enviar-servico', [AdminServiceController::class, 'enviarParaServico'])
            ->name('os.enviar');

        Route::get('/os/resultados', [AdminServiceController::class, 'resultados'])
            ->name('os.resultados');

        /*
        |--------------------------------------------------------------------------
        | NOTÍCIAS
        |--------------------------------------------------------------------------
        */
        Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
        Route::get('/noticias/create', [NoticiaController::class, 'create'])->name('noticias.create');
        Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');

        /*
        |--------------------------------------------------------------------------
        | GESTÃO DE CONTAS
        |--------------------------------------------------------------------------
        */
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [AccountManagementController::class, 'index'])->name('index');
            Route::post('/store', [AccountManagementController::class, 'store'])->name('store');
            Route::put('/update/{type}/{id}', [AccountManagementController::class, 'update'])->name('update');
            Route::delete('/delete/{type}/{id}', [AccountManagementController::class, 'destroy'])->name('destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| ANALISTA (/pbi-analista)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-analista')->name('analyst.')->group(function () {

    Route::get('/', fn () => redirect()->route('analyst.login'));

    Route::middleware(['guest:analyst', 'guard.only:analyst'])->group(function () {
        Route::get('/login', [AnalystLoginController::class, 'create'])->name('login');
        Route::post('/login', [AnalystLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:analyst', 'preventBack'])->group(function () {

        Route::post('/logout', [AnalystLoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', [ContactController::class, 'analystDashboard'])->name('dashboard');

        Route::get('/vistorias-pendentes', [ContactController::class, 'vistoriasPendentes'])
            ->name('vistorias.pendentes');

        Route::post('/gerar-os', [ContactController::class, 'storeServiceOrder'])
            ->name('os.store');

        Route::get('/profile', fn () => view('analista.profile'))->name('profile.edit');
    });
});

/*
|--------------------------------------------------------------------------
| SERVIÇO (/pbi-servico)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-servico')->name('service.')->group(function () {

    Route::get('/', fn () => redirect()->route('service.login'));

    Route::middleware(['guest:service', 'guard.only:service'])->group(function () {
        Route::get('/login', [ServiceLoginController::class, 'create'])->name('login');
        Route::post('/login', [ServiceLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:service', 'preventBack'])->group(function () {

        Route::post('/logout', [ServiceLoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/tarefas', [ServiceExecutionController::class, 'index'])->name('tasks.index');
        Route::post('/tarefas/{id}/concluir', [ServiceExecutionController::class, 'concluir'])->name('tasks.concluir');
        Route::post('/tarefas/{id}/falha', [ServiceExecutionController::class, 'falha'])->name('tasks.falha');

        Route::get('/profile', fn () => view('servico.profile'))->name('profile.edit');
    });
});

/*
|--------------------------------------------------------------------------
| ROTAS DO BREEZE
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
